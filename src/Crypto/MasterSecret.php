<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Crypto\Abstracts\VariableLengthAbstractSecret,
    WRS\Crypto\Interfaces\RandomDataInterface,
    WRS\KeyValue\Interfaces\KeyValueInterface;

class MasterSecret extends VariableLengthAbstractSecret {

    const ID_KEY = "key";
    const ID_SALT = "salt";

    private $full_id_key;
    private $full_id_salt;

    private $keyvalue;
    private $randomizer;

    public function __construct(string $name,
                                int $key_length,
                                int $salt_length,
                                KeyValueInterface $keyvalue,
                                RandomDataInterface $randomizer)
    {
        parent::__construct($name, $key_length, $salt_length);
        $this->full_id_key = sprintf("%s-%s", $this->get_name(), self::ID_KEY);
        $this->full_id_salt = sprintf("%s-%s", $this->get_name(), self::ID_SALT);
        $this->keyvalue = $keyvalue;
        $this->randomizer = $randomizer;
    }

    public function get_id_key() {
        return $this->full_id_key;
    }

    public function get_id_salt() {
        return $this->full_id_salt;
    }

    public function generate() {
        $key = $this->randomizer->get($this->get_key_length());
        $this->set_key($key);
        $salt = $this->randomizer->get($this->get_salt_length());
        $this->set_salt($salt);
    }

    /**** SecretKeeperInterface ****/

    public function set_key(string $key) {
        $this->validate_key($key);
        $this->keyvalue->set_string($this->full_id_key, $key);
    }

    public function set_salt(string $salt) {
        $this->validate_salt($salt);
        $this->keyvalue->set_string($this->full_id_salt, $salt);
    }

    public function get_key() {
        $key = $this->keyvalue->get_string($this->full_id_key);
        $this->validate_key($key);
        return $key;
    }

    public function get_salt() {
        $salt = $this->keyvalue->get_string($this->full_id_salt);
        $this->validate_salt($salt);
        return $salt;
    }
}
