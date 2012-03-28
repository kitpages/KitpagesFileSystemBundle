<?php

namespace Kitpages\FileSystemBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * KitpagesFileSystemBundleExtension
 *
 */
class KitpagesFileSystemExtension extends Extension
{
    private $factories = null;

    /**
     * Loads the extension
     *
     * @param  array            $configs
     * @param  ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();

        // first assemble the adapter factories
        $factoryConfig = new FactoryConfiguration();
        $config        = $processor->processConfiguration($factoryConfig, $configs);

        // create a service for each adapter
        $factories     = $this->createAdapterFactories($config, $container);

        // then normalize the configs
        $mainConfig = new Configuration($factories);
        $config     = $processor->processConfiguration($mainConfig, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $map = array();
        foreach ($config['file_system_list'] as $filesystem => $adapter) {
            $adapterName = array_keys($adapter);
            $adapterName = $adapterName[0];
            $adapterParameter = $adapter[$adapterName];

            if (array_key_exists($adapterName, $factories)) {
                $filesystemId      = sprintf('kitpages_file_system.file_system.%s', $filesystem);
                // create a filesystem service with his adapter
                $factories[$adapterName]->create($container, $filesystemId, $adapterParameter);
;
                $map[$filesystem] = new Reference($filesystemId);
            }


        }

        //allows calls to filesystem services with $this->get('kitpages_file_system.filesystem_map')->getAdapter(filesystemName);
        $container->getDefinition('kitpages_file_system.file_system_map')
            ->replaceArgument(0, $map);
    }

    /**
     * Creates the adapter factories
     *
     * @param  array            $config
     * @param  ContainerBuilder $container
     */
    private function createAdapterFactories($config, ContainerBuilder $container)
    {
        if (null !== $this->factories) {
            return $this->factories;
        }

        // load bundled adapter factories
        $tempContainer = new ContainerBuilder();
        $parameterBag  = $container->getParameterBag();
        $loader        = new XmlFileLoader($tempContainer, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('adapter_factories.xml');

        // load user-created adapter factories
        foreach ($config['factories'] as $factory) {
            $loader->load($parameterBag->resolveValue($factory));
        }

        $services  = $tempContainer->findTaggedServiceIds('kitpages_file_system.adapter.factory');
        $factories = array();
        foreach (array_keys($services) as $id) {
            $factory = $tempContainer->get($id);
            $factories[str_replace('-', '_', $factory->getKey())] = $factory;
        }

        return $this->factories = $factories;
    }
}
