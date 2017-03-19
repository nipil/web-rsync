<?php

class ChaCha20 {

    private $key;
    private $nonce;

    public function __construct($key, $nonce) {
        if (strlen($key) !== 32) {
            throw new Exception('Key must be a 256-bit string');
        }
        if (strlen($nonce) !== 12) {
            throw new Exception('Nonce must be a 96-bit string');
        }
        $this->key = $key;
        $this->nonce = $nonce;
    }
}

class Main {

    public function do_client() {
        $options = getopt("k");
        if (array_key_exists("k", $options)) {
            $cipher = new ChaCha20(
                "12345678901234567890123456789012",
                "123456789012"
            );
        }
    }

    public function do_server() {
    }

    public function run() {
        if (php_sapi_name() === 'cli' or defined('STDIN')) {
            $this->do_client();
        } else {
            $this->do_server();
        }
    }
}

$main = new Main();
$main->run();
