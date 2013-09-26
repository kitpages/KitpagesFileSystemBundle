<?php

namespace Kitpages\FileSystemBundle\DependencyInjection\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Local adapter factory
 *
 */
class LocalAdapterFactory implements AdapterFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $container
            ->setDefinition($id, new DefinitionDecorator('kitpages_file_system.adapter.local'))
            ->addArgument(new Reference('kitpages.util'))
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument($container->getParameter("kernel.root_dir"))
            ->addArgument($config['directory_public'])
            ->addArgument($config['directory_private'])
            ->addArgument($config['base_url'])
            ->addArgument($id)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'local';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('directory_public')->defaultValue(null)->end()
                ->scalarNode('directory_private')->defaultValue(null)->end()
                ->scalarNode('base_url')->defaultValue(null)->end()
            ->end()
        ;
    }
}
