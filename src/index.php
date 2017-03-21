<?php

/**
 * Calculates ChaCha20 blocks based on a given (key, nonce, ctr) set
 */
class ChaCha20Block {

    /**
     * ChaCha20 works on 32-bit integers
     */
    const INT_BIT_LENGTH = 32;
    const INT_BIT_MASK = 0xFFFFFFFF;

    /**
     * Define the ChaCha20 state format
     */
    const STATE_CONST_LENGTH = 4;
    const STATE_CONST_BASEINDEX = 0;
    const STATE_KEY_LENGTH = 8;
    const STATE_KEY_BASEINDEX = 4;
    const STATE_COUNTER_LENGTH = 1;
    const STATE_COUNTER_BASEINDEX = 12;
    const STATE_NONCE_LENGTH = 3;
    const STATE_NONCE_BASEINDEX = 13;

    const STATE_ARRAY_LENGTH = self::STATE_CONST_LENGTH
        + self::STATE_KEY_LENGTH
        + self::STATE_COUNTER_LENGTH
        + self::STATE_NONCE_LENGTH;

    /**
     * These are the ChaCha20 constants. They are not magical,
     * nor cryptographically designed : the represent an ascii-str
     * "expand 32-byte k" when written as 4 LITTLE-endian uint32
     */
    const CONSTANT_VALUE_0 = 0x61707865; // "expa"
    const CONSTANT_VALUE_1 = 0x3320646e; // "nd 3"
    const CONSTANT_VALUE_2 = 0x79622d32; // "2-by"
    const CONSTANT_VALUE_3 = 0x6b206574; // "te k"

    /**
     * internal state is what is built when key, nonce, ctr are modified
     */
    private $initial_state;

    /**
     * ensures that every manipulated value is truncated to integer length
     *
     * @return uint32    an integer capped at INT_BIT_MASK bits
     */
    protected function cap($value) {
        return $value & self::INT_BIT_MASK;
    }

    /**
     * initialize internal "const" values one by one
     *
     * @param int       $index      index in const range
     * @param uint32    $value      value to store
     */
    private function set_const($index, $value) {
        if ($index < 0 or $index >= self::STATE_CONST_LENGTH) {
            throw new Exception('Const index is outstide range [0.."'.self::STATE_CONST_LENGTH.']');
        }
        $this->initial_state[self::STATE_CONST_BASEINDEX + $index] = $this->cap($value);
    }

    /**
     * initialize internal "key" values one by one
     *
     * @param int       $index      index in key range
     * @param uint32    $value      value to store
     */
    public function set_key_index_uint32($index, $value) {
        if ($index < 0 or $index >= self::STATE_KEY_LENGTH) {
            throw new Exception('Key index is outstide range [0.."'.self::STATE_KEY_LENGTH.']');
        }
        $this->initial_state[self::STATE_KEY_BASEINDEX + $index] = $this->cap($value);
    }

    /**
     * initialize internal "nonce" values one by one
     *
     * @param int       $index      index in nonce range
     * @param uint32    $value      value to store
     */
    public function set_nonce_index_uint32($index, $value) {
        if ($index < 0 or $index >= self::STATE_NONCE_LENGTH) {
            throw new Exception('Nonce index is outstide range [0.."'.self::STATE_NONCE_LENGTH.']');
        }
        $this->initial_state[self::STATE_NONCE_BASEINDEX + $index] = $this->cap($value);
    }

    /**
     * initialize internal "block-counter" index
     *
     * @param uint32    $position   new block-counter index
     */
    public function set_counter($position) {
        $this->initial_state[self::STATE_COUNTER_BASEINDEX] = $this->cap($position);
    }

    /**
     * increment internal "block-counter" value by $step
     *
     * @param uint32    $step       step added to current block-counter value
     */
    public function inc_counter($step = 1) {
        $curval = $this->initial_state[self::STATE_COUNTER_BASEINDEX];
        $newval = $this->cap($curval + $step);
        $this->set_counter($newval);
    }

    /**
     * puts binary data to internal state
     *
     * extract $num little-endian uint32 from a least-significant-bit-starting
     * BINARY str named $name, and places these uint32s into internal-state
     * starting at $index
     *
     * @param   binary_string     $str    binary string holding little-endian uint32s
     */
    private function bin_to_internal($str, $name, $index, $num) {
        assert ($index > 0);
        assert ($num > 0);
        assert ($index + $num <= self::STATE_ARRAY_LENGTH);
        // check for input length
        $req_len = $num * self::INT_BIT_LENGTH >> 3;
        if (strlen($str) !== $req_len) {
            throw new Exception($name.' "'.bin2hex($str).'" is not a '.$req_len.'-bits long hex string');
        }
        // extract littl-endian uint32 from it
        $arr = unpack("V".$num, $str);
        // place nonce uint32 into the state
        array_splice($this->initial_state, $index, $num, $arr);
    }

    /**
     * set key from a least-significant-bit-starting BINARY string
     */
    public function set_key($string) {
        $this->bin_to_internal(
            $string,
            "Key",
            self::STATE_KEY_BASEINDEX,
            self::STATE_KEY_LENGTH);
    }

    /**
     * set nonce from a least-significant-bit-starting BINARY string
     */
    public function set_nonce($string) {
        $this->bin_to_internal(
            $string,
            "Nonce",
            self::STATE_NONCE_BASEINDEX,
            self::STATE_NONCE_LENGTH);
    }

    /**
     * display internal state in matrix form
     */
    public function __toString() {
        return vsprintf("00:0x%08x\t01:0x%08x\t02:0x%08x\t03:0x%08x\n04:0x%08x\t05:0x%08x\t06:0x%08x\t07:0x%08x\n08:0x%08x\t09:0x%08x\t10:0x%08x\t11:0x%08x\n12:0x%08x\t13:0x%08x\t14:0x%08x\t15:0x%08x\n", $this->initial_state);
    }

    /**
     * construct a "NULL" Block
     *
     * creates and initalize a Block.
     *
     */
    public function __construct($key=NULL, $nonce=NULL, $ctr=NULL) {
        // initialize
        $this->initial_state = array_fill(0, self::STATE_ARRAY_LENGTH, 0x00000000);
        $this->set_const(0, self::CONSTANT_VALUE_0);
        $this->set_const(1, self::CONSTANT_VALUE_1);
        $this->set_const(2, self::CONSTANT_VALUE_2);
        $this->set_const(3, self::CONSTANT_VALUE_3);
    }
}

/**
 * Main application code: runs client or server code
 */
class Main {

    public function do_client() {
        $options = getopt("k");
        if (array_key_exists("k", $options)) {
            $cipher = new ChaCha20Block();
            echo $cipher;
            $cipher->set_key(hex2bin("000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f"));
            $cipher->set_nonce(hex2bin("303132333435363738393a3b"));
            $cipher->set_counter(0x50515253);
            echo $cipher;
            $cipher->inc_counter(-3);
            echo $cipher;
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
