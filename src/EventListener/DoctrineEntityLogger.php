<?php

namespace Kikwik\DoctrineEntityLoggerBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Gedmo\Blameable\BlameableListener;
use Gedmo\IpTraceable\IpTraceableListener;
use Kikwik\DoctrineEntityLoggerBundle\Attributes\LoggableEntity;
use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;

class DoctrineEntityLogger
{

    public function __construct(
        private readonly Registry $doctrine,
        private readonly BlameableListener $blameableListener,
        private readonly IpTraceableListener $ipTraceableListener,
        private array $globalExcludedFields,
    )
    {
    }

    private array $logEntries = [];

    public function postPersist(PostPersistEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        if($this->isEntityLoggable($object))
        {
            $this->createLog(
                Log::ACTION_CREATE,
                $object,
                null,
                $this->serializeObject($object, $eventArgs->getObjectManager())
            );
        }
    }

    public function postUpdate(PostUpdateEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        if($this->isEntityLoggable($object))
        {
            $classMetadata = $eventArgs->getObjectManager()->getClassMetadata(get_class($object));

            $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();
            $oldValues = [];
            $newValues = [];

            // Changes on single fields
            foreach ($unitOfWork->getEntityChangeSet($object) as $field => $changes) {
                if ($this->isPropertyLoggable($classMetadata, $field)) {
                    if($classMetadata->isSingleValuedAssociation($field))
                    {
                        $oldValues[$field] = $this->serializeReference($changes[0]);
                        $newValues[$field] = $this->serializeReference($changes[1]);
                    }
                    else
                    {
                        $oldValues[$field] = $changes[0];
                        $newValues[$field] = $changes[1];
                    }
                }
            }

            // Changes on updated collections
            foreach ($unitOfWork->getScheduledCollectionUpdates() as $collectionUpdate) {
                if ($collectionUpdate->getOwner() === $object) {
                    $association = $collectionUpdate->getMapping()['fieldName'];
                    if ($this->isPropertyLoggable($classMetadata, $association)) {
                        $oldValues[$association] = $this->serializeCollection($collectionUpdate->getSnapshot());
                        $newValues[$association] = $this->serializeCollection($collectionUpdate->toArray());
                    }
                }
            }

            // Changes on removed collections
            foreach ($unitOfWork->getScheduledCollectionDeletions() as $collectionDeletion) {
                if ($collectionDeletion->getOwner() === $object) {
                    $association = $collectionDeletion->getMapping()['fieldName'];
                    if ($this->isPropertyLoggable($classMetadata, $association)) {
                        $oldValues[$association] = $this->serializeCollection($collectionDeletion->getSnapshot());
                        $newValues[$association] = null; // La collezione è stata eliminata
                    }
                }
            }

            if(count($oldValues) || count($newValues))
            {
                $this->createLog(
                    Log::ACTION_UPDATE,
                    $object,
                    $oldValues,
                    $newValues
                );
            }
        }

    }

    public function preRemove(PreRemoveEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        if($this->isEntityLoggable($object))
        {
            $this->createLog(
                Log::ACTION_REMOVE,
                $object,
                $this->serializeObject($object, $eventArgs->getObjectManager()),
                null
            );
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if (count($this->logEntries))
        {
            $objectManager = $eventArgs->getObjectManager();
            $connection = $objectManager->getConnection();

            /** @var Log $log */
            foreach ($this->logEntries as $log)
            {
                // save entity log with raw query to avoid recalling flush
                $logClassMetadata = $objectManager->getClassMetadata(get_class($log));
                $tableName = $logClassMetadata->getTableName();

                $data = [];
                $data[$logClassMetadata->getColumnName('action')] = $log->getAction();
                $data[$logClassMetadata->getColumnName('objectClass')] = $log->getObjectClass();
                $data[$logClassMetadata->getColumnName('objectId')] = $log->getObjectId();
                $data[$logClassMetadata->getColumnName('oldValues')] = json_encode($log->getOldValues());
                $data[$logClassMetadata->getColumnName('newValues')] = json_encode($log->getNewValues());
                $data[$logClassMetadata->getColumnName('createdAt')] = $log->getCreatedAt()->format('Y-m-d H:i:s');
                $data[$logClassMetadata->getColumnName('createdBy')] = $log->getCreatedBy();
                $data[$logClassMetadata->getColumnName('createdFromIp')] = $log->getCreatedFromIp();

                $connection->insert($tableName, $data);
            }

            $this->logEntries = [];
//            // TODO: we must clear the modification made by this listener
//            if(count($objectManager->getUnitOfWork()->getScheduledEntityInsertions())) dump($objectManager->getUnitOfWork()->getScheduledEntityInsertions());
//            if(count($objectManager->getUnitOfWork()->getScheduledEntityDeletions())) dump($objectManager->getUnitOfWork()->getScheduledEntityDeletions());
//            if(count($objectManager->getUnitOfWork()->getScheduledEntityUpdates())) dump($objectManager->getUnitOfWork()->getScheduledEntityUpdates());
//            if(count($objectManager->getUnitOfWork()->getScheduledCollectionUpdates())) dump($objectManager->getUnitOfWork()->getScheduledCollectionUpdates());
//            if(count($objectManager->getUnitOfWork()->getScheduledCollectionDeletions())) dump($objectManager->getUnitOfWork()->getScheduledCollectionDeletions());

        }
    }




    private function serializeObject(mixed $object, ObjectManager $objectManager): ?array
    {
        $result = [];
        if (!is_object($object)) {
            return null;
        }

        $classMetadata = $objectManager->getClassMetadata(get_class($object));

        // Loop over Doctrine poperties
        foreach ($classMetadata->getFieldNames() as $field)
        {
            if($this->isPropertyLoggable($classMetadata, $field))
            {
                $result[$field] = $classMetadata->getFieldValue($object, $field);
            }
        }

        // Loop over Doctrine relations
        foreach ($classMetadata->getAssociationNames() as $association)
        {
            if($this->isPropertyLoggable($classMetadata, $association))
            {
                $relatedObject = $classMetadata->getFieldValue($object, $association);

                if ($relatedObject instanceof Collection)
                {
                    // OneToMany/ManyToMany Collection
                    $relatedObjects = [];
                    foreach ($relatedObject as $item) {
                        $relatedObjects[] = $this->serializeReference($item);
                    }
                    $result[$association] = $relatedObjects;
                }
                else
                {
                    // ManyToOne relation
                    $result[$association] = $this->serializeReference($relatedObject);
                }
            }
        }

        return $result;
    }

    private function serializeReference(mixed $object): ?array
    {
        if(is_null($object))
            return null;

        return [
            'class' => str_replace('Proxies\__CG__\\', '', get_class($object)),
            'id' => method_exists($object, 'getId') ? $object->getId() : null,
            'toString' => method_exists($object, '__toString') ? (string)$object : null,
        ];
    }

    private function serializeCollection($collection): ?array
    {
        if (!$collection || !is_array($collection)) {
            return null;
        }

        $result = [];
        foreach ($collection as $item) {
            $result[] = $this->serializeReference($item);
        }

        return $result;
    }

    private function isEntityLoggable(mixed $object)
    {
        if (!is_object($object)) {
            return false;
        }

        $reflectionClass = new \ReflectionClass($object);

        // Controlla se la classe ha l'attributo Loggable
        $attributes = $reflectionClass->getAttributes(LoggableEntity::class);
        return !empty($attributes);
    }

    private function isPropertyLoggable(ClassMetadata $classMetadata, string $field): bool
    {
        if(in_array($field, $this->globalExcludedFields))
            return false;

        return true;
    }

    private function createLog(string $action, mixed $object, ?array $oldValues = null, ?array $newValues = null): void
    {
        if($object instanceof Log)
            return;

        $log = new Log();
        $log->setAction($action);
        $log->setObjectClass(str_replace('Proxies\__CG__\\', '', get_class($object)));
        $log->setObjectId($object->getId());
        $log->setOldValues($oldValues);
        $log->setNewValues($newValues);
        $log->setCreatedAt(new \DateTime());
        $logClassMetadata = $this->doctrine->getManager()->getClassMetadata(Log::class);
        $log->setCreatedBy($this->blameableListener->getFieldValue($logClassMetadata, 'createdBy', null));
        $log->setCreatedFromIp($this->ipTraceableListener->getFieldValue($logClassMetadata, 'createdFromIp', null));
        $this->logEntries[] = $log;
    }


}