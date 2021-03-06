<?php

declare(strict_types=1);

namespace WRS\Utils;

class StringToInt
{
    public static function convert(string $input)
    {
        // verify that number is actually an integer
        $valid = preg_match('/^[-+]?[[:digit:]]+$/', $input);
        if ($valid === 0) {
            throw new \InvalidArgumentException(sprintf("Invalid integer %s", $input));
        }

        // test if bigger than max
        $res = bccomp($input, sprintf("%d", PHP_INT_MAX));
        if ($res === 1) {
            throw new \RangeException(sprintf("Integer too large %s", $input));
        }

        // test if smaller than min
        $res = bccomp($input, sprintf("%d", PHP_INT_MIN));
        if ($res === -1) {
            throw new \RangeException(sprintf("Integer too large %s", $input));
        }

        // with the above validations, conversion cannot fail
        $result = sscanf($input, "%d", $value);
        return $value;
    }
}
