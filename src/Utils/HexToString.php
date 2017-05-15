<?php

declare(strict_types=1);

namespace WRS\Utils;

class HexToString
{
    public static function convert(string $hex_str, int $req_len = null)
    {
        $bin = @hex2bin($hex_str);
        if ($bin === false) {
            throw new \Exception(sprintf("Invalid hex string %s", $hex_str));
        }
        if ($req_len === null) {
            return $bin;
        }
        if (strlen($bin) !== $req_len) {
            throw new \Exception(sprintf("Invalid length %d for %s", $req_len, $hex_str));
        }
        return $bin;
    }
}
