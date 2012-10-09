<?php

namespace Kitpages\FileSystemBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Main configuration for the Gaufrette DIC extension
 *
 */
class Configuration implements ConfigurationInterface
{
    private $factories;

    /**
     * Constructor
     *
     * @param  array $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * Generates the configuration tree builder
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kitpages_file_system');

        $this->addMainSection($rootNode, $this->factories);

        $rootNode
            // add a faux-entry for factories, so that no validation error is thrown
            ->fixXmlConfig('factory', 'factories')
            ->children()
                ->arrayNode('factories')->ignoreExtraKeys()->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function addMainSection(ArrayNodeDefinition $node, array $factories)
    {
        $adapterNodeBuilder = $node
            ->children()
                ->arrayNode('file_system_list')
                    ->useAttributeAsKey('filesystem')
                    ->prototype('array')
                        ->useAttributeAsKey('adapter')
                            ->performNoDeepMerging()
                            ->children()
        ;

        foreach ($factories as $name => $factory) {
            $factoryNode = $adapterNodeBuilder->arrayNode($name)->canBeUnset();

            $factory->addConfiguration($factoryNode);
        }
    }

}
