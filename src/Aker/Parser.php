<?php

namespace Aker;

class Parser
{
    private $sym;

    private $tokenizer;

    public function __construct(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    private function accept($s)
    {
        if ($this->sym[0] === $s) {
            $this->nextsym();
            return true;
        }
        return false;
    }

    private function expect($s)
    {
        if ($this->accept($s)) {
            return true;
        }
        throw new \Exception("expect: unexpected symbol " . $s);
    }

    private function current($s)
    {
        return $this->sym[0] == $s || $this->sym[0] == Tokens::T_EOF;
    }

    private function nextsym()
    {
        $this->sym = $this->tokenizer->getNextToken();
    }

    private function literal()
    {
        if ($this->accept(Tokens::T_NUMBER)) {
            return true;
        }

        if ($this->accept(Tokens::T_TRUE)) {
            return true;
        }

        return false;
    }

    private function expression()
    {
        if ($this->literal()) {
            if ($this->accept(Tokens::T_ADD)) {
                $this->expression();
                return;
            }

            if ($this->accept(Tokens::T_SUBTRACT)) {
                $this->expression();
                return;
            }

            $this->expect(Tokens::T_SEMICOLON);
            return;
        }

        throw new \Exception("expression: syntax error, unexpected token: " . $this->sym[1]);
    }

    private function classItem()
    {
        if ($this->accept(Tokens::T_PRIVATE)) {

            if ($this->accept(Tokens::T_IDENTIFIER)) {
                if ($this->accept(Tokens::T_EQUALS)) {
                    $this->expect(Tokens::T_NUMBER);
                }
                $this->expect(Tokens::T_SEMICOLON);
                return;
            }

            if ($this->accept(Tokens::T_FUNCTION)) {
                return;
            }

            throw new \Exception("class-item: syntax error, unexpected token: " . $this->sym[1]);
        }

        if ($this->accept(Tokens::T_PROTECTED)) {

            if ($this->accept(Tokens::T_IDENTIFIER)) {
                if ($this->accept(Tokens::T_EQUALS)) {
                    $this->expect(Tokens::T_NUMBER);
                }
                $this->expect(Tokens::T_SEMICOLON);
                return;
            }

            if ($this->accept(Tokens::T_FUNCTION)) {
                return;
            }

            throw new \Exception("class-item: syntax error, unexpected token: " . $this->sym[1]);
        }

        if ($this->accept(Tokens::T_PUBLIC)) {

            if ($this->accept(Tokens::T_IDENTIFIER)) {
                if ($this->accept(Tokens::T_EQUALS)) {
                    $this->expect(Tokens::T_NUMBER);
                }
                $this->expect(Tokens::T_SEMICOLON);
                return;
            }

            if ($this->accept(Tokens::T_FUNCTION)) {
                $this->expect(Tokens::T_IDENTIFIER);
                $this->expect(Tokens::T_LPAREN);
                $this->expect(Tokens::T_RPAREN);
                $this->expect(Tokens::T_LBRACKET);
                $this->expression();
                $this->expect(Tokens::T_RBRACKET);
                return;
            }

            throw new \Exception("class-item: syntax error, unexpected token: " . $this->sym[1]);
        }

        throw new \Exception("class-item: syntax error, unexpected token: " . $this->sym[1]);
    }

    private function main()
    {
        if ($this->accept(Tokens::T_NAMESPACE)) {
            $this->expect(Tokens::T_IDENTIFIER);
            $this->expect(Tokens::T_SEMICOLON);
            return;
        }

        if ($this->accept(Tokens::T_CLASS)) {
            $this->expect(Tokens::T_IDENTIFIER);
            $this->expect(Tokens::T_LBRACKET);
            while (!$this->current(Tokens::T_RBRACKET))
                $this->classItem();
            $this->expect(Tokens::T_RBRACKET);
            return;
        }

        throw new \Exception("main: syntax error " . print_r($this->sym, true));
    }

    public function parse()
    {
        $this->nextsym();
        while (!$this->current(Tokens::T_EOF))
            $this->main();

        return true;
    }
}
