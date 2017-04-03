<?php

namespace Aker;

class Tokens
{
    const T_NAMESPACE = 0;
    const T_IDENTIFIER = 1;
    const T_SEMICOLON = 2;
    const T_LBRACKET = 3;
    const T_RBRACKET = 4;
    const T_CLASS = 5;
    const T_NUMBER = 6;
    const T_WHITESPACE = 7;
    const T_PRIVATE = 8;
    const T_PROTECTED = 9;
    const T_PUBLIC = 10;
    const T_EQUALS = 11;
    const T_FUNCTION = 12;
    const T_LPAREN = 13;
    const T_RPAREN = 14;
    const T_EOF = 100;
}
