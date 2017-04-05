<?php

namespace Aker\Builder;

class ClassMethod
{
    private $name;

    private $methods;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setStatementBlock(StatementBlock $block)
    {
        $this->block = $block;
    }
}
