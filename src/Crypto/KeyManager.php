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

    public function derive_key(int $req_len, string $additionnal_info = "") {
        if ($req_len <= 0) {
            throw new \Exception("Invalid length requested for derived key");
        }

        // extract phase (with 2.1 note : 'IKM' is used as the HMAC input, not as the HMAC key)
        $prk = hash_hmac(self::HASH_FUNCTION, $this->get_key(), $this->get_salt(), TRUE);

        // handles different hashing functions
        $len = strlen($prk);
        $n_iter = ceil($req_len / $len);

        // expand phase RFC 5869
        $final_output = "";
        $iteration_output = "";
        for ($i = 1; $i <= $n_iter; $i++) {
            $iteration_input = $iteration_output . $additionnal_info . $i;
            $iteration_output = hash_hmac(self::HASH_FUNCTION, $iteration_input, $prk, TRUE);
            $final_output .= $iteration_output;
        }

        // retain only requested length byte
        return substr($final_output, 0, $req_len);
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
