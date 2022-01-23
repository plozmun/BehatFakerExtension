<?php

declare(strict_types=1);

namespace Plozmun\FakerExtension\Parser;

use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\ScenarioNode;

final class ScenarioParser
{
    /**
     * @var StepParser
     */
    private $stepParser;

    public function __construct(StepParser $stepParser)
    {
        $this->stepParser = $stepParser;
    }

    public function parse(ScenarioInterface $scenario): ScenarioNode
    {
        $steps = [];
        foreach ($scenario->getSteps() as $step) {
            $steps[] = $this->stepParser->parse($step);
        }
        return new ScenarioNode(
            $scenario->getTitle(),
            $scenario->getTags(),
            $steps,
            $scenario->getKeyword(),
            $scenario->getLine()
        );
    }
}
