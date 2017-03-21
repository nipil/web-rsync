<?php

// see rfc7539
class ChaCha20 {

    const KEY_LENGTH = 256;
    const NONCE_LENGTH = 96;

    private $key_little_endian_hex_string;
    private $nonce_little_endian_hex_string;
    private $counter_uint32;

    private $initial_state;
    private $current_state;

    public function set_key_from_hex_string($little_endian_string_of_hex_digits) {
        if (strlen($little_endian_string_of_hex_digits) !== self::KEY_LENGTH / 4) {
            throw new Exception('Key "'
                .$little_endian_string_of_hex_digits
                .'" is not a '
                .self::KEY_LENGTH
                .'-bits long hex string');
        }
        $this->key_little_endian_hex_string = $little_endian_string_of_hex_digits;
    }

    public function set_nonce_from_hex_string($little_endian_string_of_hex_digits) {
        if (strlen($little_endian_string_of_hex_digits) !== self::NONCE_LENGTH / 4) {
            throw new Exception('Nonce "'
                .$little_endian_string_of_hex_digits
                .'" is not a '
                .self::NONCE_LENGTH
                .'-bits long hex string');
        }
        $this->nonce_little_endian_hex_string = $little_endian_string_of_hex_digits;
    }

    public function set_counter_uint32($counter_uint32) {
        $this->counter_uint32 = $counter_uint32 & 0xFFFFFFFF;
    }

    public function inc_counter_uint32($counter_uint32) {
        $this->counter_uint32 = ($this->counter_uint32 + 1) & 0xFFFFFFFF;
    }

    public function build_state() {
        $this->initial_state = array_fill(0, 16, 0x00000000);
    }

    public function reset_state() {
        $this->current_state = $this->initial_state;
    }

    public function compute_block() {
        // TODO
    }

    public function __toString() {
        $tmp = "";
        echo "matrix\n";
        for ($i=0; $i<4; $i++) {
            for ($j=0; $j<4; $j++) {
                $index = $i * 4 + $j;
                $value = $this->current_state[$index];
                $tmp .= sprintf("%02d=1x%08x\t", $index, $value);
            }
            $tmp .= "\n";
        }
        return $tmp;
    }

    public function __construct($key, $nonce, $counter) {
        $this->set_key_from_hex_string($key);
        $this->set_nonce_from_hex_string($nonce);
        $this->set_counter_uint32($counter);
        $this->build_state();
        $this->reset_state();
    }
}

class Main {

    public function check_int_size() {
        echo "PHP_INT_SIZE=".PHP_INT_SIZE."\n";
    }

    public function do_client() {
        $options = getopt("k");
        if (array_key_exists("k", $options)) {
            $cipher = new ChaCha20(
                "000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f",
                "404142434445464748494a4b",
                0);
            var_dump($cipher);
            echo $cipher;
        }
    }

    public function do_server() {
    }

    public function run() {
        $this->check_int_size();
        if (php_sapi_name() === 'cli' or defined('STDIN')) {
            $this->do_client();
        } else {
            $this->do_server();
        }
    }
}

$main = new Main();
$main->run();
