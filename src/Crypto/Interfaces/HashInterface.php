<?php

declare(strict_types=1);

namespace WRS\Crypto\Interfaces;

interface HashInterface {
    public function hash(string $message);
    public function hmac(string $message, string $key);
}
