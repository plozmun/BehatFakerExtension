<?php

declare(strict_types=1);

namespace Behat\FakerExtension\Transformer;

use Behat\Gherkin\Node\PyStringNode;
use Faker\Factory;

class PyStringNodeTransformer
{
    public static function transform(PyStringNode $node, string $locale): PyStringNode
    {
        $faker = Factory::create($locale);

        $strings = array_map(function (string $s) use ($faker) {
            return $faker->parse($s);
        }, $node->getStrings());

        return new PyStringNode($strings, $node->getLine());
    }
}