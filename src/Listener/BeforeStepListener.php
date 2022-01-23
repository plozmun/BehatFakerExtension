<?php

namespace Behat\FakerExtension\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeStepListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::BEFORE => ['setUp', 0],
        ];
    }

    public function setUp()
    {
        dd(func_get_args());
    }
}