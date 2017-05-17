<?php

declare(strict_types=1);

namespace WRS\Crypto\Interfaces;

interface MasterSecretInterface extends KeyAccessorInterface
{
    public function setSalt(string $salt);
    public function getSalt();

    public function generate(int $key_length, int $salt_length);
}
