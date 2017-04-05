<?php

namespace Tyson\Builder;

class ClassDefinition
{
    private $name;

    private $methods;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setMethods($methods)
    {
        $this->methods = $methods;
    }
}
