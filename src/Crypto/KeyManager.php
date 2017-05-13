<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Apps\App,
    WRS\Crypto\SecretKeeperInterface,
    WRS\KeyValue\KeyValueInterface;

class KeyManager implements SecretKeeperInterface {

    const MASTER_KEY_LENGTH_BITS = 1 << 12;
    const MASTER_KEY_LENGTH_BYTES = self::MASTER_KEY_LENGTH_BITS >> 3;
    const MASTER_SALT_LENGTH_BYTES = 1 << 3;
    const HASH_FUNCTION = "sha512";

    const CONFIG_NAME_KEY = "master-key";
    const CONFIG_NAME_SALT = "master-salt";

    private $logger;
    private $config;

    public function create_master() {
        $this->set_key(random_bytes(self::MASTER_KEY_LENGTH_BYTES));
        $this->set_salt(random_bytes(self::MASTER_SALT_LENGTH_BYTES));
    }

    protected function bin_to_hex(string $name, int $req_len, string $bin) {
        $hex = bin2hex($bin);
        $len = strlen($bin);
        if ($len != $req_len) {
            throw new \Exception(sprintf("Invalid length for %s : %s", $name, $len));
        }
        $this->config->set_string($name, $hex);
    }

    public function set_key(string $key) {
        return $this->bin_to_hex(self::CONFIG_NAME_KEY, self::MASTER_KEY_LENGTH_BYTES, $key);
    }

    public function set_salt(string $salt) {
        return $this->bin_to_hex(self::CONFIG_NAME_SALT, self::MASTER_SALT_LENGTH_BYTES, $salt);
    }

    protected function hex_to_bin(string $name, int $req_len) {
        $hex = $this->config->get_string($name);
        $bin = @hex2bin($hex);
        if ($bin === FALSE) {
            throw new \Exception(sprintf("Invalid hex string %s : %s", $name, $hex));
        }
        $len = strlen($bin);
        if ($len != $req_len) {
            throw new \Exception(sprintf("Invalid length for %s : %s", $name, $len));
        }
        return $bin;
    }

    public function get_key() {
        return $this->hex_to_bin(self::CONFIG_NAME_KEY, self::MASTER_KEY_LENGTH_BYTES);
    }

    public function get_salt() {
        return $this->hex_to_bin(self::CONFIG_NAME_SALT, self::MASTER_SALT_LENGTH_BYTES);
    }

    public function __construct(KeyValueInterface $config) {
        $this->logger = App::GetLogger();
        $this->config = $config;
    }
}
