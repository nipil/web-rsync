<?php

declare(strict_types=1);

namespace WRS\Crypto\Abstracts;

use WRS\Crypto\Interfaces\SecretKeeperInterface;

abstract class VariableLengthAbstractSecret implements SecretKeeperInterface
{
    private $name;
    private $key_length;
    private $salt_length;

    public function __construct(string $name, int $key_length, int $salt_length)
    {
        if (strlen($name) === 0) {
            throw new \Exception("Invalid name");
        }
        if ($key_length <= 0) {
            throw new \Exception(sprintf("Invalid key length : %d", $key_length));
        }
        if ($salt_length <= 0) {
            throw new \Exception(sprintf("Invalid salt length : %d", $salt_length));
        }
        $this->name = $name;
        $this->key_length = $key_length;
        $this->salt_length = $salt_length;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getKeyLength()
    {
        return $this->key_length;
    }

    public function getSaltLength()
    {
        return $this->salt_length;
    }

    public function validateKey($key)
    {
        $len = strlen($key);
        if ($len !== $this->getKeyLength()) {
            throw new \Exception(sprintf("Invalid key length : %d", $len));
        }
        return true;
    }

    public function validateSalt($salt)
    {
        $len = strlen($salt);
        if ($len !== $this->getSaltLength()) {
            throw new \Exception(sprintf("Invalid salt length : %d", $len));
        }
        return true;
    }
}
