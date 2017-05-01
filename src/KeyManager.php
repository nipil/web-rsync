<?php

declare(strict_types=1);

namespace WRS;

class KeyManager {

    const MASTER_KEY_LENGTH_BITS = 1 << 12;
    const MASTER_KEY_LENGTH_BYTES = self::MASTER_KEY_LENGTH_BITS >> 3;
    const MASTER_KEY_LENGTH_BYTES_STEP = 1 << 3;

    const MASTER_KEY_FILE = "wrs_private_key.txt";

    private $logger = NULL;

    private $master_key = NULL;

    public function create_master_key() {
        $this->logger->info(sprintf("Please wait while generating key"));
        $this->master_key = random_bytes(self::MASTER_KEY_LENGTH_BYTES);
        $this->master_key_hex = bin2hex($this->master_key);
        $res = file_put_contents(
            self::MASTER_KEY_FILE,
            $this->master_key_hex);
        if ($res === FALSE) {
            throw new \Exception(sprintf(
                "Could not write key to file %s",
                self::MASTER_KEY_FILE));
        }
        $this->logger->info(sprintf(
            "Private key stored in file %s",
            self::MASTER_KEY_FILE));
    }

    public function __construct(Arguments $args) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__);
    }

}
