<?php

declare(strict_types=1);

namespace Plozmun\FakerExtension\Transformer;

use Behat\Gherkin\Node\TableNode;

class TableNodeTransformer
{
    public static function transform(TableNode $tableNode, string $locale): TableNode
    {
        return $tableNode;
    }
}
