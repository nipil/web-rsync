<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Crypto\Interfaces\RandomDataInterface;

class NativeRandomizer implements RandomDataInterface
{
    public function get(int $length)
    {
        if ($length < 0) {
            throw new \InvalidArgumentException(sprintf("Invalid number of bytes requested : %d", $length));
        }
        if ($length === 0) {
            // random_bytes ERRORs with length 0
            return "";
        }
        return random_bytes($length);
    }
}
