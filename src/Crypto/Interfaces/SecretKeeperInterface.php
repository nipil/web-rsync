<?php

declare(strict_types=1);

namespace WRS\Crypto\Interfaces;

interface SecretKeeperInterface
{
    public function setKey(string $key);
    public function setSalt(string $salt);
    public function getKey();
    public function getSalt();
}
