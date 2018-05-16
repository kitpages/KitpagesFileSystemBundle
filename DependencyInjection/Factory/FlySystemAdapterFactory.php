<?php

namespace Kitpages\FileSystemBundle\DependencyInjection\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Flysystem adapter factory
 *
 */
class FlySystemAdapterFactory implements AdapterFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $container
            ->setDefinition($id, new DefinitionDecorator('kitpages_file_system.adapter.flysystem'))
            ->addArgument(new Reference('kitpages.util'))
            ->addArgument(new Reference($config['flysystem_adapter']))
            ->addArgument($config['file_uri_prefix'])
            ->addArgument($id)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'flysystem';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('flysystem_adapter')->defaultValue(null)->end()
                ->scalarNode('file_uri_prefix')->defaultValue(null)->end()
            ->end()
        ;
    }
}
