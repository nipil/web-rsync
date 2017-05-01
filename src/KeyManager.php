<?php

declare(strict_types=1);

namespace WRS;

class KeyManager {

    const MASTER_KEY_LENGTH_BITS = 1 << 12;
    const MASTER_KEY_LENGTH_BYTES = self::MASTER_KEY_LENGTH_BITS >> 3;

    const MASTER_SALT_LENGTH_BYTES = 1 << 3;

    const MASTER_KEY_FILE = "wrs_private_key.txt";
    const MASTER_SALT_FILE = "wrs_master_salt.txt";

    private $logger = NULL;

    private $master_key;
    private $master_salt;

    private function create_to_file(&$target_bytes,
                                    int $byte_length,
                                    string $file) {
        $this->logger->debug(__METHOD__);
        if ($byte_length <= 0) {
            throw new \Exception(sprintf(
                "Invalid length",
                $byte_length));
        }
        if ($file === NULL) {
            throw new \Exception(sprintf(
                "Invalid file name",
                $file));
        }
        $target_bytes = random_bytes($byte_length);
        $data_hex = bin2hex($target_bytes);
        $res = file_put_contents($file, $data_hex);
        if ($res === FALSE) {
            throw new \Exception(sprintf(
                "Could not write random data to file %s",
                $file));
        }
    }

    protected function create_master_key() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Please wait while generating master key...");
        $this->create_to_file(
            $this->master_key,
            self::MASTER_KEY_LENGTH_BYTES,
            self::MASTER_KEY_FILE);
        $this->logger->info(sprintf(
            "Private key stored in file %s",
            self::MASTER_KEY_FILE));
    }

    protected function create_master_salt() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Please wait while generating master salt...");
        $this->create_to_file(
            $this->master_salt,
            self::MASTER_SALT_LENGTH_BYTES,
            self::MASTER_SALT_FILE);
        $this->logger->info(sprintf(
            "Master salt stored in file %s",
            self::MASTER_SALT_FILE));
    }

    public function create_master() {
        $this->logger->debug(__METHOD__);
        $this->create_master_key();
        $this->create_master_salt();
    }

    public function __construct(Arguments $args) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__);
    }
}
