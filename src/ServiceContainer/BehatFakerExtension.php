<?php

namespace Plozmun\FakerExtension\ServiceContainer;

use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
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
        $builder->children()->scalarNode('locale')->defaultValue('en');
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('behat_faker.locale', $config['locale']);
        $this->loadParser($container);
        $definition = new Definition('Plozmun\FakerExtension\Loader\FakerLoader', [
            new Reference('gherkin.loader.gherkin_file'),
            new Reference('behat_faker.parser.feature'),
        ]);
        $definition->addTag(GherkinExtension::LOADER_TAG, array('priority' => 100));
        $container->setDefinition('faker_behat.loader', $definition);
    }

    private function loadParser(ContainerBuilder $container): void
    {
        $definition = new Definition('Plozmun\FakerExtension\Parser\StepParser', [
            $container->getParameter('behat_faker.locale'),
        ]);
        $container->setDefinition('behat_faker.parser.step', $definition);

        $definition = new Definition('Plozmun\FakerExtension\Parser\ScenarioParser', [
            new Reference('behat_faker.parser.step'),
        ]);
        $container->setDefinition('behat_faker.parser.scenario', $definition);

        $definition = new Definition('Plozmun\FakerExtension\Parser\FeatureParser', [
            new Reference('behat_faker.parser.scenario'),
        ]);
        $container->setDefinition('behat_faker.parser.feature', $definition);
    }

    public function getConfigKey(): string
    {
        return 'faker';
    }
}
