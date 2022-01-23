<?php

namespace Behat\FakerExtension\ServiceContainer;

use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BehatFakerExtension implements Extension
{
    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadParser($container);
        $definition = new Definition('Behat\FakerExtension\Loader\FakerLoader', [
            new Reference('gherkin.loader.gherkin_file'),
            new Reference('behat_faker.parser.feature'),
        ]);
        $definition->addTag(GherkinExtension::LOADER_TAG, array('priority' => 100));
        $container->setDefinition('faker_behat.loader', $definition);
    }

    private function loadParser(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\FakerExtension\Parser\StepParser');
        $container->setDefinition('behat_faker.parser.step', $definition);

        $definition = new Definition('Behat\FakerExtension\Parser\ScenarioParser', [
            new Reference('behat_faker.parser.step'),
        ]);
        $container->setDefinition('behat_faker.parser.scenario', $definition);

        $definition = new Definition('Behat\FakerExtension\Parser\FeatureParser', [
            new Reference('behat_faker.parser.scenario'),
        ]);
        $container->setDefinition('behat_faker.parser.feature', $definition);
    }

    public function getConfigKey(): string
    {
        return 'faker';
    }
}