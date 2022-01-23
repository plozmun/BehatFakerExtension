<?php

declare(strict_types=1);

namespace Behat\FakerExtension\Parser;

use Behat\FakerExtension\Transformer\PyStringNodeTransformer;
use Behat\FakerExtension\Transformer\TableNodeTransformer;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;

final class StepParser
{
    public function parse(StepNode $step): StepNode
    {
        return new StepNode(
            $step->getKeyword(),
            $this->parseText($step->getText()),
            $this->parseArguments($step->getArguments()),
            $step->getLine(),
            $step->getKeywordType()
        );
    }

    private function parseText(string $text)
    {
        return $text;
    }

    private function parseArguments(array $arguments): array
    {
        return array_map(function (ArgumentInterface $argument) {
            if ($argument instanceof TableNode) {
                return TableNodeTransformer::transform($argument);
            }
            if ($argument instanceof PyStringNode) {
                return PyStringNodeTransformer::transform($argument);
            }
        }, $arguments);
    }
}
