<?php

require 'src/Aker/Tokens.php';
require 'src/Aker/Tokenizer.php';
require 'src/Aker/Parser.php';

use Aker\Parser;
use Aker\Tokens;
use Aker\Tokenizer;

$rules = [
    Tokens::T_NUMBER     => '\d+',
    Tokens::T_WHITESPACE => '\s+',
    Tokens::T_CLASS      => 'class',
    Tokens::T_NAMESPACE  => 'namespace',
    Tokens::T_PRIVATE    => 'private',
    Tokens::T_PROTECTED  => 'protected',
    Tokens::T_PUBLIC     => 'public',
    Tokens::T_FUNCTION   => 'function',
    Tokens::T_TRUE       => 'true',
    Tokens::T_FALSE      => 'false',
    Tokens::T_NULL       => 'null',
    Tokens::T_IDENTIFIER => "[\_a-zA-Z][\_a-zA-Z0-9]*",
    Tokens::T_SEMICOLON  => ";",
    Tokens::T_LBRACKET   => "{",
    Tokens::T_RBRACKET   => "}",
    Tokens::T_EQUALS     => "=",
    Tokens::T_LPAREN     => '\(',
    Tokens::T_RPAREN     => '\)',
    Tokens::T_ADD        => '\+',
    Tokens::T_SUBTRACT   => '\-',
];

$program = "

namespace X;

class Y
{
    public R;

    private X = 100;

    private X;

    public function __construct()
    {
        (2 + 3);
    }
}

";

(new Parser(new Tokenizer($rules, $program)))->parse();
