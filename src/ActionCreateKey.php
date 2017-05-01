<?php

declare(strict_types=1);

namespace WRS;

class ActionCreateKey extends Action {

    const KEY_LENGTH_BITS = 1 << 12;
    const KEY_LENGTH_BYTES = self::KEY_LENGTH_BITS >> 3;
    const KEY_LENGTH_BYTES_STEP = 1 << 3;

    const KEY_FILE = "wrs_private_key.txt";

    private $logger = NULL;
    private $key = NULL;

    public function __construct(Arguments $args) {
        $this->logger = \Logger::getLogger(get_class($this));
        $this->logger->debug(__METHOD__);
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info(sprintf("Please wait while generating key"));
        $this->key = random_bytes(self::KEY_LENGTH_BYTES);
        $this->key_hex = bin2hex($this->key);
        $res = file_put_contents(self::KEY_FILE, $this->key_hex);
        if ($res === FALSE) {
            throw new \Exception(sprintf(
                "Could not write key to file %s",
                self::KEY_FILE));
        }
        $this->logger->info(sprintf(
            "Private key stored in file %s",
            self::KEY_FILE));
    }
}
