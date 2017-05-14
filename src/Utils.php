<?php

declare(strict_types=1);

namespace WRS;

class Utils {

    public static function StringToInt(string $input) {
        // verify that number is actually an integer
        $valid = preg_match('/^[-+]?[[:digit:]]+$/', $input);
        if ($valid === 0) {
            throw new \Exception(sprintf(
                "Invalid integer %s",
                $input));
        }

        // test if bigger than max
        $res = bccomp($input, sprintf("%d", PHP_INT_MAX));
        if ($res === 1) {
            throw new \Exception(sprintf(
                "Integer too large %s",
                $input));
        }

        // test if smaller than min
        $res = bccomp($input, sprintf("%d", PHP_INT_MIN));
        if ($res === -1) {
            throw new \Exception(sprintf(
                "Integer too large %s",
                $input));
        }

        // with the above validations, conversion cannot fail
        $result = sscanf($input, "%d", $value);
        return $value;
    }

    public function HexToString(string $hex_str, int $req_len = NULL) {
        $bin = @hex2bin($hex_str);
        if ($bin === FALSE) {
            throw new \Exception(sprintf("Invalid hex string %s", $hex_str));
        }
        if ($req_len === NULL) {
            return $bin;
        }
        if (strlen($bin) !== $req_len) {
            throw new \Exception(sprintf("Invalid length %d for %s", $req_len, $hex_str));
        }
        return $bin;
    }
}
