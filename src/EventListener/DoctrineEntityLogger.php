<?php

namespace Kikwik\DoctrineEntityLoggerBundle\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\Persistence\ObjectManager;
use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Symfony\Component\Serializer\SerializerInterface;

class DoctrineEntityLogger
{

    private array $logEntries = [];

    public function postPersist(PostPersistEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $this->createLog(Log::ACTION_CREATE, $object, null, $this->serializeObject($object, $eventArgs->getObjectManager()));
    }

    public function postUpdate(PostUpdateEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();
        $oldValues = [];
        $newValues = [];
        foreach($unitOfWork->getEntityChangeSet($object) as $field => $changes)
        {
            $oldValues[$field] = $changes[0];
            $newValues[$field] = $changes[1];
        }
        $this->createLog(Log::ACTION_UPDATE, $object, $oldValues, $newValues);
    }

    public function preRemove(PreRemoveEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $this->createLog(Log::ACTION_REMOVE, $object, $this->serializeObject($object, $eventArgs->getObjectManager()), null);
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
        foreach ($classMetadata->getFieldNames() as $field) {
            $result[$field] = $classMetadata->getFieldValue($object, $field);
        }

        // Itera sulle relazioni OneToOne e ManyToOne
        foreach ($classMetadata->getAssociationNames() as $association) {
            $relatedObject = $classMetadata->getFieldValue($object, $association);

            if (is_object($relatedObject)) {
                $result[$association] = [
                    'class' => get_class($relatedObject),
                    'id' => method_exists($relatedObject, 'getId') ? $relatedObject->getId() : null,
                    'toString' => method_exists($relatedObject, '__toString') ? (string)$relatedObject : null,
                ];
            } elseif (is_iterable($relatedObject)) { // Gestisce le collezioni OneToMany/ManyToMany
                $relatedObjects = [];
                foreach ($relatedObject as $item) {
                    $relatedObjects[] = [
                        'class' => get_class($item),
                        'id' => method_exists($item, 'getId') ? $item->getId() : null,
                        'toString' => method_exists($item, '__toString') ? (string)$item : null,
                    ];
                }
                $result[$association] = $relatedObjects;
            } else {
                $result[$association] = null;
            }
        }

        return $result;
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