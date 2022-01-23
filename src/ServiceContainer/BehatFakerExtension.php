<?php

namespace Behat\FakerExtension\ServiceContainer;

use Behat\FakerExtension\Listener\BeforeStepListener;
use Behat\Testwork\ServiceContainer\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BehatFakerExtension implements Extension
{
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(BeforeStepListener::class);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);

        $container->setDefinition('behat_faker.step_listener', $definition);
    }

    public function getConfigKey()
    {
        return 'faker';
    }
}