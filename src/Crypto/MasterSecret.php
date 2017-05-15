<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Crypto\Abstracts\VariableLengthAbstractSecret;
use WRS\Crypto\Interfaces\RandomDataInterface;
use WRS\KeyValue\Interfaces\KeyValueInterface;

class MasterSecret extends VariableLengthAbstractSecret
{
    const ID_KEY = "key";
    const ID_SALT = "salt";

    private $full_id_key;
    private $full_id_salt;

    private $keyvalue;
    private $randomizer;

    public function __construct(
        string $name,
        int $key_length,
        int $salt_length,
        KeyValueInterface $keyvalue,
        RandomDataInterface $randomizer
    ) {
        parent::__construct($name, $key_length, $salt_length);
        $this->full_id_key = sprintf("%s-%s", $this->getName(), self::ID_KEY);
        $this->full_id_salt = sprintf("%s-%s", $this->getName(), self::ID_SALT);
        $this->keyvalue = $keyvalue;
        $this->randomizer = $randomizer;
    }

    public function getIdKey()
    {
        return $this->full_id_key;
    }

    public function getIdSalt()
    {
        return $this->full_id_salt;
    }

    public function generate()
    {
        $key = $this->randomizer->get($this->getKeyLength());
        $this->setKey($key);
        $salt = $this->randomizer->get($this->getSaltLength());
        $this->setSalt($salt);
    }

    /**** SecretKeeperInterface ****/

    public function setKey(string $key)
    {
        $this->validateKey($key);
        $this->keyvalue->setString($this->full_id_key, $key);
    }

    public function setSalt(string $salt)
    {
        $this->validateSalt($salt);
        $this->keyvalue->setString($this->full_id_salt, $salt);
    }

    public function getKey()
    {
        $key = $this->keyvalue->getString($this->full_id_key);
        $this->validateKey($key);
        return $key;
    }

    public function getSalt()
    {
        $salt = $this->keyvalue->getString($this->full_id_salt);
        $this->validateSalt($salt);
        return $salt;
    }
}
