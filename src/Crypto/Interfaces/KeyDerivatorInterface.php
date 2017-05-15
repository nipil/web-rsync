<?php

declare(strict_types=1);

namespace WRS\Crypto\Interfaces;

interface KeyDerivatorInterface
{
    public function deriveKey(int $byte_length, string $info);
}
