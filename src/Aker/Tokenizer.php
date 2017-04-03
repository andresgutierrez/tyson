<?php

namespace Aker;

class Tokenizer
{
    private $rules;
	private $pattern;
    private $tokens;
    private $position = 0;

	public function __construct(array $rules, $program)
	{
		$this->pattern = '#(' . join(')|(', $rules) . ')#A';
		$this->rules = array_keys($rules);
        $this->tokens = $this->tokenize($program);
	}

	public function tokenize($program)
	{
		preg_match_all($this->pattern, $program, $matches, PREG_SET_ORDER);

		$len = 0;
        $tokens = [];
		$count = count($this->rules);
		foreach ($matches as $match) {
			$rule = null;
			for ($i = 1; $i <= $count; $i++) {
				if (!isset($match[$i])) {
					break;
				} else {
                    if ($match[$i] != null) {
    					$rule = $this->rules[$i - 1];
                        break;
    				}
                }
			}
            if ($rule != Tokens::T_WHITESPACE) {
			   $tokens[] = array($rule, $match[0], $len);
            }

			$len += strlen($match[0]);
		}

		if ($len !== strlen($program)) {
			$error = $len;
		}

        $tokens[] = array(Tokens::T_EOF, null, 0);

		if (isset($error)) {
			list($line, $col) = $this->getPosition($program, $error);
			$token = str_replace("\n", '\n', substr($program, $error, 10));
			throw new \Exception("Unexpected '$token' on line $line, column $col");
		}

		return $tokens;
	}

	private function getPosition($text, $offset)
	{
		$text = substr($text, 0, $offset);
		return array(substr_count($text, "\n") + 1, $offset - strrpos("\n" . $text, "\n") + 1);
	}

    public function getNextToken()
    {
        return $this->tokens[$this->position++];
    }
}
