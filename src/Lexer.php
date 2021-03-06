<?php

namespace Expressions;

class Lexer
{
    const TOKEN = [
        '/^("(?:[^"\\\\]|\\\\.)*")/' => "T_STRING",
        '/^(\d+(\.\d+)?)/' => "T_NUMBER",
        '/^(false)/' => "T_FALSE",
        '/^(true)/' => "T_TRUE",
        '/^(&&|\\/\\\\|and)/' => "T_CONJUNCTION",
        '/^(\\\\\\/|or)/' => "T_DISJUNCTION",
        '/^(===|<->|<=>)/' => "T_EQUALITY",
        '/^(!==|!<->|!<=>|<>)/' => "T_NOT_EQUALITY",
        '/^(==)/' => "T_EQUALS",
        '/^(!=)/' => "T_NOT_EQUALS",
        '/^(=>|->)/' => "T_IMPLICATION",
        '/^(xor)/' => "T_XOR",
        '/^(>=)/' => "T_GREATEREQUAL",
        '/^(<=)/' => "T_LESSEQUAL",
        '/^(>)/' => "T_GREATER",
        '/^(<)/' => "T_LESS",
        '/^(~|!)/' => "T_NOT",
        '/^(\-)/' => "T_MINUS",
        '/^(\()/' => "T_LEFTPAREN",
        '/^(\))/' => "T_RIGHTPAREN"
    ];

    /**
     * @param string $expression_string
     * @return array An array of tokens
     * @throws ExpressionException
     */
    public static function lex($expression_string)
    {
        $tokens = [];

        $offset = 0;
        while ($offset < strlen($expression_string)) {
            list($match, $token) = self::match($expression_string, $offset, $offset_start);
            $tokens[] = new Token($match, $token, $offset_start);
        }

        return $tokens;
    }

    /**
     * @param $expression_string
     * @param $offset
     * @param $offset_start
     * @return array
     * @throws ExpressionException
     */
    public static function match($expression_string, &$offset, &$offset_start)
    {
        $string = substr($expression_string, $offset);
        $trimmed_string = ltrim($string);

        $trim_size = strlen($string) - strlen($trimmed_string);

        $offset_start = $offset + $trim_size;

        foreach (self::TOKEN as $regex_pattern => $token_identifier) {
            if (preg_match($regex_pattern, $trimmed_string, $matches) === 1) {
                $offset += ($trim_size + strlen($matches[1]));

                return [
                    $matches[1],
                    $token_identifier
                ];
            }
        }

        throw new ExpressionException(
            "expressions-unidentified-token",
            [Expressions::highlightSegment($expression_string, $offset + $trim_size, 1)]
        );
    }
}