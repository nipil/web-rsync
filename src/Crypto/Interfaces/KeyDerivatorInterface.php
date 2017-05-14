<?php

declare(strict_types=1);

namespace WRS\Crypto\Interfaces;

interface KeyDerivatorInterface {
    public function derive_key(int $byte_length, string $info);
}
