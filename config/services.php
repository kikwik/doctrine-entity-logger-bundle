<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;



use Kikwik\DoctrineEntityLoggerBundle\EventListener\DoctrineEntityLogger;

return static function (ContainerConfigurator $container): void {

    $container->services()
        ->set('kikwik_doctrine_entity_logger.event_listener.doctrine_entity_logger', DoctrineEntityLogger::class)
        ->args([
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
};
