<?php

namespace Tyson\Builder;

class StatementBlock
{
    private $statements = [];

    public function add(StatamentInterface $statement)
    {
        $this->statements[] = $statement;
    }
}
