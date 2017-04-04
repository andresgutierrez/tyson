<?php

namespace Aker;

use Aker\Builder\ClassDefinition;

class Generator
{
    private $ebnf;

    private $root;

    public function __construct()
    {

    }

    public function load($path)
    {
        $this->ebnf = json_decode(file_get_contents($path), true);

        if (!isset($this->ebnf['bnf'])) {
            throw new \Exception('no bnf node');
        }

        foreach ($this->ebnf['bnf'] as $production => $rules) {

        }

        $this->generateSkeleton();
    }

    private function generateSkeleton()
    {
        $classDefinition = new ClassDefinition('Parser');
    }
}
