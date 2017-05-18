<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Crypto\Interfaces\MasterSecretInterface;
use WRS\Crypto\Interfaces\RandomDataInterface;
use WRS\KeyValue\Interfaces\KeyValueInterface;

class MasterSecret implements MasterSecretInterface
{
    const ID_KEY = "key";
    const ID_SALT = "salt";

    private $full_id_key;
    private $full_id_salt;

    private $keyvalue;
    private $randomizer;

    public function __construct(
        string $name,
        KeyValueInterface $keyvalue,
        RandomDataInterface $randomizer
    ) {
        $this->name = $name;
        $this->full_id_key = sprintf("%s-%s", $this->getName(), self::ID_KEY);
        $this->full_id_salt = sprintf("%s-%s", $this->getName(), self::ID_SALT);
        $this->keyvalue = $keyvalue;
        $this->randomizer = $randomizer;
    }

    public function getName()
    {
        return $this->name;
    }


    public function getIdKey()
    {
        return $this->full_id_key;
    }

    public function getIdSalt()
    {
        return $this->full_id_salt;
    }

    /* MasterSecretInterface */

    public function generate(int $key_length, int $salt_length)
    {
        if ($key_length <= 0) {
            throw new \InvalidArgumentException(sprintf("Invalid key length : %d", $key_length));
        }
        if ($salt_length <= 0) {
            throw new \InvalidArgumentException(sprintf("Invalid salt length : %d", $salt_length));
        }
        $key = $this->randomizer->get($key_length);
        $salt = $this->randomizer->get($salt_length);
        $this->setKey($key);
        $this->setSalt($salt);
    }

    public function setKey(string $key)
    {
        if (strlen($key) === 0) {
            throw new \InvalidArgumentException("Key cannot be empty");
        }
        $this->keyvalue->setString($this->full_id_key, $key);
    }

    public function setSalt(string $salt)
    {
        if (strlen($salt) === 0) {
            throw new \InvalidArgumentException("Salt cannot be empty");
        }
        $this->keyvalue->setString($this->full_id_salt, $salt);
    }

    public function getKey()
    {
        $key = $this->keyvalue->getString($this->full_id_key);
        if (strlen($key) === 0) {
            throw new \InvalidArgumentException("Key cannot be empty");
        }
        return $key;
    }

    public function getSalt()
    {
        $salt = $this->keyvalue->getString($this->full_id_salt);
        if (strlen($salt) === 0) {
            throw new \InvalidArgumentException("Salt cannot be empty");
        }
        return $salt;
    }
}
