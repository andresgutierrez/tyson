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
            $current = $this->sym;
            $this->nextsym();
            return $current;
        }
        return false;
    }

    private function expect($symbol)
    {
        if ($current = $this->accept($symbol)) {
            return $current;
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

    private function backtrack()
    {
        $this->sym = $this->tokenizer->discard();
    }

    private function literal()
    {
        if ($expr = $this->accept(Tokens::T_NUMBER)) {
            return $expr;
        }

        if ($expr = $this->accept(Tokens::T_TRUE)) {
            return $expr;
        }

        if ($expr = $this->accept(Tokens::T_FALSE)) {
            return $expr;
        }

        return false;
    }

    private function binaryExpression($left)
    {
        if ($this->accept(Tokens::T_ADD)) {
            $right = $this->expression();
            return ['type' => 'add', 'left' => $left, 'right' => $right];
        }

        if ($this->accept(Tokens::T_SUBTRACT)) {
            $right = $this->expression();
            return ['type' => 'sub', 'left' => $left, 'right' => $right];
        }

        return $left;
    }

    private function expression()
    {
        if ($this->accept(Tokens::T_SUBTRACT)) {
            $right = $this->expression();
            return ['type' => 'minus', 'right' => $right];
        }

        if ($this->accept(Tokens::T_LPAREN)) {
            $left = $this->expression();
            $this->expect(Tokens::T_RPAREN);
            return $this->binaryExpression($left);
        }

        if ($literal = $this->literal()) {
            return $this->binaryExpression($literal);
        }

        throw new \Exception("expression: syntax error, unexpected token: " . $this->sym[1]);
    }

    private function expressionStatement()
    {
        $expression = $this->expression();
        $this->expect(Tokens::T_SEMICOLON);
        return ['type' => 'expr', 'expr' => $expression];
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

            $this->backtrack();
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

            $this->backtrack();
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

            $this->backtrack();
            return false;
        }

        return false;
    }

    private function acceptMethod()
    {
        if ($this->accept(Tokens::T_PRIVATE)) {
            if ($this->accept(Tokens::T_FUNCTION)) {
                $name = $this->expect(Tokens::T_IDENTIFIER);
                $this->expect(Tokens::T_LPAREN);
                $this->expect(Tokens::T_RPAREN);
                $this->expect(Tokens::T_LBRACKET);
                $expr = $this->expressionStatement();
                $this->expect(Tokens::T_RBRACKET);
                return ['type' => 'method', 'name' => $name, 'statements' => $expr];
            }
            return false;
        }

        if ($this->accept(Tokens::T_PROTECTED)) {
            if ($this->accept(Tokens::T_FUNCTION)) {
                $name = $this->expect(Tokens::T_IDENTIFIER);
                $this->expect(Tokens::T_LPAREN);
                $this->expect(Tokens::T_RPAREN);
                $this->expect(Tokens::T_LBRACKET);
                $expr = $this->expressionStatement();
                $this->expect(Tokens::T_RBRACKET);
                return ['type' => 'method', 'name' => $name, 'statements' => $expr];
            }
            return false;
        }

        if ($this->accept(Tokens::T_PUBLIC)) {
            if ($this->accept(Tokens::T_FUNCTION)) {
                $name = $this->expect(Tokens::T_IDENTIFIER);
                $this->expect(Tokens::T_LPAREN);
                $this->expect(Tokens::T_RPAREN);
                $this->expect(Tokens::T_LBRACKET);
                $expr = $this->expressionStatement();
                $this->expect(Tokens::T_RBRACKET);
                return ['type' => 'method', 'name' => $name, 'statements' => $expr];
            }
        }

        return false;
    }

    private function acceptClassItem()
    {
        if ($property = $this->acceptProperty()) {
            return $property;
        }

        if ($method = $this->acceptMethod()) {
            return $method;
        }

        throw new \Exception("class-item: syntax error, unexpected token: " . $this->sym[1]);
    }

    private function acceptNamespace()
    {
        if ($this->accept(Tokens::T_NAMESPACE)) {
            $name = $this->expect(Tokens::T_IDENTIFIER);
            $this->expect(Tokens::T_SEMICOLON);
            return ['type' => 'namespace', 'name' => $name];
        }

        return false;
    }

    private function acceptClass()
    {
        if ($this->accept(Tokens::T_CLASS)) {
            $name = $this->expect(Tokens::T_IDENTIFIER);
            $this->expect(Tokens::T_LBRACKET);

            $items = [];
            while (!$this->current(Tokens::T_RBRACKET))
                $items[] = $this->acceptClassItem();

            $this->expect(Tokens::T_RBRACKET);

            return ['type' => 'class', 'name' => $name, 'items' => $items];
        }

        return false;
    }

    private function main()
    {
        if ($namespace = $this->acceptNamespace()) {
            return $namespace;
        }

        if ($class = $this->acceptClass()) {
            return $class;
        }

        throw new \Exception("main: syntax error " . print_r($this->sym, true));
    }

    public function parse()
    {
        $this->nextsym();

        $items = [];
        while (!$this->current(Tokens::T_EOF))
            $items[] = $this->main();

        print_r($items);
        return true;
    }
}
