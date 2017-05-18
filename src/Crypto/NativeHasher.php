<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Crypto\Interfaces\HashInterface;

class NativeHasher implements HashInterface
{
    private $hash_function;

    public function __construct(string $hash_function)
    {
        if (!in_array($hash_function, hash_algos(), true)) {
            throw new \UnexpectedValueException(sprintf("Invalid hash function : %s", $hash_function));
        }
        $this->hash_function = $hash_function;
    }

    public function getHashFunction()
    {
        return $this->hash_function;
    }

    public function hash(string $message)
    {
        return hash($this->hash_function, $message, true);
    }

    public function hmac(string $message, string $key)
    {
        return hash_hmac($this->hash_function, $message, $key, true);
    }
}
