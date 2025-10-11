<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;



use Kikwik\DoctrineEntityLoggerBundle\EventListener\DoctrineEntityLogger;
use Kikwik\DoctrineEntityLoggerBundle\Service\EntityLoggerConfig;

return static function (ContainerConfigurator $container): void {

    $container->services()
        ->set('kikwik_doctrine_entity_logger.event_listener.logger', DoctrineEntityLogger::class)
        ->args([
            service('kikwik_doctrine_entity_logger.service.config'),
            service('doctrine'),
            service('stof_doctrine_extensions.listener.blameable'),
            service('stof_doctrine_extensions.listener.ip_traceable'),
            abstract_arg('Global excluded fields'),
        ])
        ->tag('doctrine.event_listener', ['event' => 'postPersist'])
        ->tag('doctrine.event_listener', ['event' => 'postUpdate'])
        ->tag('doctrine.event_listener', ['event' => 'preRemove'])
        ->tag('doctrine.event_listener', ['event' => 'postFlush'])
    ;

    $container->services()
        ->set('kikwik_doctrine_entity_logger.service.config',EntityLoggerConfig::class)
        ->args([
            abstract_arg('enabled'),
        ])
        ->alias(EntityLoggerConfig::class,'kikwik_doctrine_entity_logger.service.config')
    ;
};
