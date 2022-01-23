<?php

declare(strict_types=1);

namespace Behat\FakerExtension\Parser;

use Behat\Gherkin\Node\FeatureNode;

final class FeatureParser
{
    /**
     * @var ScenarioParser
     */
    private $scenarioParser;

    public function __construct(ScenarioParser $scenarioParser)
    {
        $this->scenarioParser = $scenarioParser;
    }

    public function parse(FeatureNode $feature): FeatureNode
    {
        $scenarios = [];
        foreach ($feature->getScenarios() as $scenario) {
            $scenarios[] = $this->scenarioParser->parse($scenario);
        }
        return new FeatureNode(
            $feature->getTitle(),
            $feature->getDescription(),
            $feature->getTags(),
            $feature->getBackground(),
            $scenarios,
            $feature->getKeyword(),
            $feature->getLanguage(),
            $feature->getFile(),
            $feature->getLine(),
        );
    }
}
