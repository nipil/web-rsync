<?php

declare(strict_types=1);

namespace WRS\Crypto\Interfaces;

interface KeyAccessorInterface
{
    public function setKey(string $key);
    public function getKey();
}
