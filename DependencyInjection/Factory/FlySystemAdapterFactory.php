<?php

namespace Kitpages\FileSystemBundle\DependencyInjection\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $class = class_exists('\Symfony\Component\DependencyInjection\ChildDefinition')
            ? '\Symfony\Component\DependencyInjection\ChildDefinition'
            : '\Symfony\Component\DependencyInjection\DefinitionDecorator';

        $container
            ->setDefinition($id, new $class('kitpages_file_system.adapter.flysystem'))
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
