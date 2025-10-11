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
                ->booleanNode('enabled')->defaultTrue()->end()
                ->arrayNode('global_excluded_fields')
                    ->scalarPrototype()->end()
                    ->defaultValue(['createdAt', 'updatedAt', 'createdBy', 'updatedBy', 'createdFromIp', 'updatedFromIp'])
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $builder->getDefinition('kikwik_doctrine_entity_logger.event_listener.logger')
            ->setArgument(4, $config['global_excluded_fields']);

        $builder->getDefinition('kikwik_doctrine_entity_logger.service.config')
            ->setArgument(0, $config['enabled']);
    }




}