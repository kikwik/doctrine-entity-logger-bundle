<?php

namespace Kikwik\DoctrineEntityLoggerBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class DoctrineEntityLogger
{

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();

    }

}