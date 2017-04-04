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

    private function accept($symbol)
    {
        if ($this->sym[0] === $symbol) {
            $this->nextsym();
            return true;
        }
        return false;
    }

    private function expect($symbol)
    {
        if ($this->accept($symbol)) {
            return true;
        }
        throw new \Exception("expect: unexpected symbol " . $symbol);
    }

    private function current($symbol)
    {
        return $this->sym[0] == $symbol || $this->sym[0] == Tokens::T_EOF;
    }

    private function nextsym()
    {
        $this->sym = $this->tokenizer->getNextToken();
    }

    private function discard()
    {
        $this->sym = $this->tokenizer->discard();
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
        if ($this->accept(Tokens::T_LPAREN)) {
            $this->expression();
            $this->expect(Tokens::T_RPAREN);
            return;
        }

        if ($this->literal()) {
            if ($this->accept(Tokens::T_ADD)) {
                $this->expression();
                return;
            }

            if ($this->accept(Tokens::T_SUBTRACT)) {
                $this->expression();
                return;
            }
            return;
        }

        throw new \Exception("expression: syntax error, unexpected token: " . $this->sym[1]);
    }

    private function expressionStatement()
    {
        $this->expression();
        $this->expect(Tokens::T_SEMICOLON);
    }

    private function acceptProperty()
    {
        if ($this->accept(Tokens::T_PRIVATE)) {
            if ($this->accept(Tokens::T_IDENTIFIER)) {
                if ($this->accept(Tokens::T_EQUALS)) {
                    $this->expect(Tokens::T_NUMBER);
                }
                $this->expect(Tokens::T_SEMICOLON);
                return true;
            }

            $this->discard();
            return false;
        }

        if ($this->accept(Tokens::T_PROTECTED)) {
            if ($this->accept(Tokens::T_IDENTIFIER)) {
                if ($this->accept(Tokens::T_EQUALS)) {
                    $this->expect(Tokens::T_NUMBER);
                }
                $this->expect(Tokens::T_SEMICOLON);
                return true;
            }

            $this->discard();
            return false;
        }

        if ($this->accept(Tokens::T_PUBLIC)) {

            if ($this->accept(Tokens::T_IDENTIFIER)) {
                if ($this->accept(Tokens::T_EQUALS)) {
                    $this->expect(Tokens::T_NUMBER);
                }
                $this->expect(Tokens::T_SEMICOLON);
                return true;
            }

            $this->discard();
            return false;
        }

        return false;
    }

    private function acceptMethod()
    {
        if ($this->accept(Tokens::T_PRIVATE)) {
            if ($this->accept(Tokens::T_FUNCTION)) {
                $this->expect(Tokens::T_IDENTIFIER);
                $this->expect(Tokens::T_LPAREN);
                $this->expect(Tokens::T_RPAREN);
                $this->expect(Tokens::T_LBRACKET);
                $this->expressionStatement();
                $this->expect(Tokens::T_RBRACKET);
                return true;
            }
            return false;
        }

        if ($this->accept(Tokens::T_PROTECTED)) {
            if ($this->accept(Tokens::T_FUNCTION)) {
                $this->expect(Tokens::T_IDENTIFIER);
                $this->expect(Tokens::T_LPAREN);
                $this->expect(Tokens::T_RPAREN);
                $this->expect(Tokens::T_LBRACKET);
                $this->expressionStatement();
                $this->expect(Tokens::T_RBRACKET);
                return true;
            }
            return false;
        }

        if ($this->accept(Tokens::T_PUBLIC)) {
            if ($this->accept(Tokens::T_FUNCTION)) {
                $this->expect(Tokens::T_IDENTIFIER);
                $this->expect(Tokens::T_LPAREN);
                $this->expect(Tokens::T_RPAREN);
                $this->expect(Tokens::T_LBRACKET);
                $this->expressionStatement();
                $this->expect(Tokens::T_RBRACKET);
                return true;
            }
        }

        return false;
    }

    private function acceptClassItem()
    {
        if ($this->acceptProperty()) {
            return;
        }

        if ($this->acceptMethod()) {
            return;
        }

        throw new \Exception("class-item: syntax error, unexpected token: " . $this->sym[1]);
    }

    private function acceptNamespace()
    {
        if ($this->accept(Tokens::T_NAMESPACE)) {
            $this->expect(Tokens::T_IDENTIFIER);
            $this->expect(Tokens::T_SEMICOLON);
            return true;
        }

        return false;
    }

    private function acceptClass()
    {
        if ($this->accept(Tokens::T_CLASS)) {
            $this->expect(Tokens::T_IDENTIFIER);
            $this->expect(Tokens::T_LBRACKET);
            while (!$this->current(Tokens::T_RBRACKET))
                $this->acceptClassItem();
            $this->expect(Tokens::T_RBRACKET);
            return true;
        }

        return false;
    }

    private function main()
    {
        if ($this->acceptNamespace()) {
            return;
        }

        if ($this->acceptClass()) {
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
