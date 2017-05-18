<?php

declare(strict_types=1);

namespace WRS\Utils;

class HexToString
{
    public static function convert(string $hex_str, int $req_len = null)
    {
        $bin = @hex2bin($hex_str);
        if ($bin === false) {
            throw new \InvalidArgumentException(sprintf("Invalid hex string %s", $hex_str));
        }
        if ($req_len === null) {
            return $bin;
        }
        if ($req_len < 0) {
            throw new \InvalidArgumentException(sprintf("Invalid required length %d", $req_len));
        }
        if (strlen($bin) !== $req_len) {
            throw new \LengthException(sprintf("Input hex does not validate required length %d", $req_len));
        }
        return $bin;
    }
}
