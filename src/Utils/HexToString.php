<?php

declare(strict_types=1);

namespace WRS\Utils;

class HexToString {

    public function convert(string $hex_str, int $req_len = NULL) {
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
