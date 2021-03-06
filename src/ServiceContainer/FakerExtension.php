<?php

namespace Plozmun\FakerExtension\ServiceContainer;

use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FakerExtension implements Extension
{
    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('locale')->defaultValue('en')->end()
                ->scalarNode('cache')
                    ->info('Sets the faker parser cache folder')
                    ->defaultValue(
                        is_writable(sys_get_temp_dir())
                        ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'faker_behat_cache'
                        : null
                    )
                ->end()
            ->end()
        ;
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('faker_behat.locale', $config['locale']);
        $this->loadDefaultLoaders($container, $config['cache']);
    }

    /**
     * Loads gherkin loaders.
     *
     * @param ContainerBuilder $container
     * @param string           $cachePath
     */
    private function loadDefaultLoaders(ContainerBuilder $container, $cachePath)
    {
        if ($cachePath) {
            $cacheDefinition = new Definition('Behat\Gherkin\Cache\FileCache', array($cachePath));
        } else {
            $cacheDefinition = new Definition('Behat\Gherkin\Cache\MemoryCache');
        }

        $definition = new Definition('Plozmun\FakerExtension\Loader\FakerFileLoader', array(
            new Reference('gherkin.parser'),
            $cacheDefinition,
            $container->getParameter('faker_behat.locale'),
        ));

        $definition->addMethodCall('setBasePath', ['%paths.base%']);
        $definition->addTag(GherkinExtension::LOADER_TAG, array('priority' => 100));
        $container->setDefinition('faker_behat.loader', $definition);
    }

    public function getConfigKey(): string
    {
        return 'faker';
    }
}
