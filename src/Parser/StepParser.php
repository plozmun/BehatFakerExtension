<?php

declare(strict_types=1);

namespace Plozmun\FakerExtension\Parser;

use Plozmun\FakerExtension\Transformer\PyStringNodeTransformer;
use Plozmun\FakerExtension\Transformer\TableNodeTransformer;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Faker\Factory;

final class StepParser
{
    /**
     * @var string
     */
    private $locale;
    
    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

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

    private function parseText(string $text): string
    {
        $faker = Factory::create($this->locale);

        return $faker->parse($text);
    }

    /**
     * @param array<int, ArgumentInterface> $arguments
     * @return array<int, ArgumentInterface>
     */
    private function parseArguments(array $arguments): array
    {
        return array_map(function (ArgumentInterface $argument) {
            if ($argument instanceof TableNode) {
                return TableNodeTransformer::transform($argument, $this->locale);
            }
            if ($argument instanceof PyStringNode) {
                return PyStringNodeTransformer::transform($argument, $this->locale);
            }
        }, $arguments);
    }
}
