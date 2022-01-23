<?php

declare(strict_types=1);

namespace Behat\FakerExtension\Transformer;

use Behat\Gherkin\Node\TableNode;

class TableNodeTransformer
{
    public static function transform(TableNode $tableNode): TableNode
    {
        return $tableNode;
    }
}