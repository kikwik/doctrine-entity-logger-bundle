<?php

namespace Kikwik\DoctrineEntityLoggerBundle\EventListener;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\ObjectManager;
use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;

class DoctrineEntityLogger
{

    private array $logEntries = [];

    private array $updatedObjects = [];

    public function postPersist(PostPersistEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $this->createLog(
            Log::ACTION_CREATE,
            $object,
            null,
            $this->serializeObject($object, $eventArgs->getObjectManager())
        );
    }

    public function postUpdate(PostUpdateEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $classMetadata = $eventArgs->getObjectManager()->getClassMetadata(get_class($object));

        $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();
        $oldValues = [];
        $newValues = [];

        // Gestione dei campi semplici
        foreach ($unitOfWork->getEntityChangeSet($object) as $field => $changes) {
            if ($this->isLoggable($classMetadata, $field)) {
                if($classMetadata->isSingleValuedAssociation($field))
                {
                    $oldValues[$field] = is_null($changes[0])
                        ? null
                        : [
                            'class' => str_replace('Proxies\__CG__\\', '', get_class($changes[0])),
                            'id' => method_exists($changes[0], 'getId') ? $changes[0]->getId() : null,
                            'toString' => method_exists($changes[0], '__toString') ? (string)$changes[0] : null,
                        ];

                    $newValues[$field] = is_null($changes[1])
                        ? null
                        : [
                            'class' => str_replace('Proxies\__CG__\\', '', get_class($changes[1])),
                            'id' => method_exists($changes[1], 'getId') ? $changes[1]->getId() : null,
                            'toString' => method_exists($changes[1], '__toString') ? (string)$changes[1] : null,
                        ];
                }
                else
                {
                    $oldValues[$field] = $changes[0];
                    $newValues[$field] = $changes[1];
                }
            }
        }

        // Cambiamenti sulle collezioni aggiornate
        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collectionUpdate) {
            if ($collectionUpdate->getOwner() === $object) {
                $association = $collectionUpdate->getMapping()['fieldName'];
                if ($this->isLoggable($classMetadata, $association)) {
                    $oldValues[$association] = $this->serializeCollection($collectionUpdate->getSnapshot());
                    $newValues[$association] = $this->serializeCollection($collectionUpdate->toArray());
                }
            }
        }

        // Collezioni pianificate per eliminazione
        foreach ($unitOfWork->getScheduledCollectionDeletions() as $collectionDeletion) {
            if ($collectionDeletion->getOwner() === $object) {
                $association = $collectionDeletion->getMapping()['fieldName'];
                if ($this->isLoggable($classMetadata, $association)) {
                    $oldValues[$association] = $this->serializeCollection($collectionDeletion->getSnapshot());
                    $newValues[$association] = null; // La collezione è stata eliminata
                }
            }
        }


        $this->createLog(
            Log::ACTION_UPDATE,
            $object,
            $oldValues,
            $newValues
        );
    }

    public function preRemove(PreRemoveEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $this->createLog(
            Log::ACTION_REMOVE,
            $object,
            $this->serializeObject($object, $eventArgs->getObjectManager())
        );
    }


    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if(count($this->logEntries))
        {
            $objectManager = $eventArgs->getObjectManager();
            foreach($this->logEntries as $log)
            {
                $objectManager->persist($log);
            }

            $this->logEntries = [];
            $objectManager->flush();
        }
    }

    private function serializeObject(mixed $object, ObjectManager $objectManager): ?array
    {
        $result = [];
        if (!is_object($object)) {
            return null;
        }

        $classMetadata = $objectManager->getClassMetadata(get_class($object));

        // Itera sulle proprietà dichiarate da Doctrine
        foreach ($classMetadata->getFieldNames() as $field)
        {
            if($this->isLoggable($classMetadata, $field))
            {
                $result[$field] = $classMetadata->getFieldValue($object, $field);
            }
        }

        // Itera sulle relazioni
        foreach ($classMetadata->getAssociationNames() as $association)
        {
            if($this->isLoggable($classMetadata, $association))
            {
                $relatedObject = $classMetadata->getFieldValue($object, $association);

                if ($relatedObject instanceof Collection)
                {
                    // Gestisce le collezioni OneToMany/ManyToMany
                    $relatedObjects = [];
                    foreach ($relatedObject as $item) {
                        $relatedObjects[] = [
                            'class' => str_replace('Proxies\__CG__\\', '', get_class($item)),
                            'id' => method_exists($item, 'getId') ? $item->getId() : null,
                            'toString' => method_exists($item, '__toString') ? (string)$item : null,
                        ];
                    }
                    $result[$association] = $relatedObjects;
                }
                elseif (is_object($relatedObject))
                {
                    // Entità singola
                    $result[$association] = [
                        'class' => str_replace('Proxies\__CG__\\', '', get_class($relatedObject)),
                        'id' => method_exists($relatedObject, 'getId') ? $relatedObject->getId() : null,
                        'toString' => method_exists($relatedObject, '__toString') ? (string)$relatedObject : null,
                    ];
                }
                else
                {
                    $result[$association] = null;
                }
            }
        }

        return $result;
    }

    private function serializeCollection($collection): ?array
    {
        if (!$collection || !is_array($collection)) {
            return null;
        }

        $result = [];
        foreach ($collection as $item) {
            $result[] = [
                'class' => str_replace('Proxies\__CG__\\', '', get_class($item)),
                'id' => method_exists($item, 'getId') ? $item->getId() : null,
                'toString' => method_exists($item, '__toString') ? (string)$item : null,
            ];
        }

        return $result;
    }



    private function isLoggable(object $classMetadata, string $field): bool
    {
        if(in_array($field, ['createdAt', 'updatedAt','createdBy','updatedBy','createdFromIp','updatedFromIp']))
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
        $this->logEntries[] = $log;
    }


}