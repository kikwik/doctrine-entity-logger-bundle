<?php

namespace Kikwik\DoctrineEntityLoggerBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class KikwikDoctrineEntityLoggerBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/packages/stof_doctrine_extensions.yaml');
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('global_excluded_fields')
                    ->scalarPrototype()->end() // Per indicare che gli elementi dell'array devono essere valori scalari
                    ->defaultValue(['createdAt', 'updatedAt', 'createdBy', 'updatedBy', 'createdFromIp', 'updatedFromIp'])
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.xml');

        $container->services()
            ->get('kikwik_doctrine_entity_logger.event_listener.doctrine_entity_logger')
            ->arg('$globalExcludedFields', $config['global_excluded_fields'])
        ;
    }




}