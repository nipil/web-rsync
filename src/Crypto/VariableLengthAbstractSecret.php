<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Crypto\SecretKeeperInterface;

abstract class VariableLengthAbstractSecret implements SecretKeeperInterface {

    private $name;

    private $key_length;

    private $salt_length;

    public function __construct(string $name, int $key_length, int $salt_length) {
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

    public function get_name() {
        return $this->name;
    }

    public function get_key_length() {
        return $this->key_length;
    }

    public function get_salt_length() {
        return $this->salt_length;
    }
}
