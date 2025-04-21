<?php

namespace Kikwik\DoctrineEntityLoggerBundle\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;

class DoctrineEntityLogger
{

    public function __construct(
        private SerializerInterface $serializer
    )
    {
    }

    private array $logEntries = [];

    public function postPersist(PostPersistEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();
        $this->createLog(Log::ACTION_INSERT, $object, $unitOfWork->getEntityChangeSet($object));
    }

    public function postUpdate(PostUpdateEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();
        $this->createLog(Log::ACTION_UPDATE, $object, $unitOfWork->getEntityChangeSet($object));
    }

    public function preRemove(PreRemoveEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        $this->createLog(Log::ACTION_DELETE, $object, json_decode($this->serializer->serialize($object, 'json'), true));
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

    private function createLog(string $action, mixed $object, array $changeSet = null): void
    {
        if($object instanceof Log)
            return;

        $log = new Log();
        $log->setAction($action);
        $log->setObjectClass(str_replace('Proxies\__CG__\\', '', get_class($object)));
        $log->setObjectId($object->getId());
        $log->setChanges($changeSet);
        $this->logEntries[] = $log;
    }


}