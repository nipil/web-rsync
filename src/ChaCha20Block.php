<?php

declare(strict_types=1);

namespace WRS;

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
     * Enum for state selection
     */
    const STATE_INTERNAL = 0;
    const STATE_PRE_FINAL = 1;
    const STATE_FINAL = 2;

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
     * Number of full (column + diagonal) iterations
     */
    const FULL_QUARTER_ROUND_ITERATIONS = 10;

    /**
     * internal state is what is built when key, nonce, ctr are modified
     */
    private $initial_state;

    /**
     * intermediary state when rounds are done
     */
    private $intermediary_state;

    /**
     * final state is the output of the calculation
     */
    private $final_state;

    /**
     * ensures that every manipulated value is truncated to integer length
     *
     * @return uint32    an integer capped at INT_BIT_MASK bits
     */
    public static function cap(int $value) : int {
        return $value & self::INT_BIT_MASK;
    }

    /**
     * rotate bits to the left by $n bits
     *
     * @param uint32    $value      value to rotate
     * @param int       $left       number of slots to rotate
     *
     * @return uint32   $value rotated $nbits to the left
     */
    public static function rot_left(int $value, int $left) : int {
        if ($left < 0 or $left >= self::INT_BIT_LENGTH) {
            throw new ChaCha20Exception(sprintf("Left bitwise-rotation %d is outstide range [0..%d[", $left, self::INT_BIT_LENGTH));
        }
        $first = $value;
        $lp = self::cap($value << $left);
        $rp = self::cap($value >> (self::INT_BIT_LENGTH - $left));
        $value = $lp | $rp;
        return $value;
    }

    /**
     * does a bitwise XOR between the arguments
     *
     * @return uint32    an integer capped at INT_BIT_MASK bits
     */
    public static function xor(int $a, int $b) : int {
        return $a ^ $b;
    }

    /**
     * add two integers and cap the sum to the required number of bits
     *
     * @return uint32    an integer capped at INT_BIT_MASK bits
     */
    public static function add_cap(int $a, int $b) : int {
        return self::cap($a + $b);
    }

    /**
     * initialize initial state's "const" values one by one
     *
     * @param int       $index      index in const range
     * @param uint32    $value      value to store
     */
    public function set_const_index_value(int $index, int $value) /* add ': void' in php 7.1 */ {
        if ($index < 0 or $index >= self::STATE_CONST_LENGTH) {
            throw new ChaCha20Exception(sprintf("Const index %d is outstide range [0..%d[", $index, self::STATE_CONST_LENGTH.'['));
        }
        $this->initial_state[self::STATE_CONST_BASEINDEX + $index] = $this->cap($value);
    }

    /**
     * initialize initial state's "key" values one by one
     *
     * @param int       $index      index in key range
     * @param uint32    $value      value to store
     */
    public function set_key_index_value(int $index, int $value) /* add ': void' in php 7.1 */ {
        if ($index < 0 or $index >= self::STATE_KEY_LENGTH) {
            throw new ChaCha20Exception(sprintf("Key index %d is outstide range [0..%d[", $index, self::STATE_KEY_LENGTH.'['));
        }
        $this->initial_state[self::STATE_KEY_BASEINDEX + $index] = $this->cap($value);
    }

    /**
     * initialize initial state's "nonce" values one by one
     *
     * @param int       $index      index in nonce range
     * @param uint32    $value      value to store
     */
    public function set_nonce_index_value(int $index, int $value) /* add ': void' in php 7.1 */ {
        if ($index < 0 or $index >= self::STATE_NONCE_LENGTH) {
            throw new ChaCha20Exception(sprintf("Nonce index %d is outstide range [0..%d[", $index, self::STATE_NONCE_LENGTH.'['));
        }
        $this->initial_state[self::STATE_NONCE_BASEINDEX + $index] = $this->cap($value);
    }

    /**
     * initialize initial state's "block-counter" index
     *
     * @param uint32    $position   new block-counter index
     */
    public function set_counter(int $position) /* add ': void' in php 7.1 */ {
        $this->initial_state[self::STATE_COUNTER_BASEINDEX] = $this->cap($position);
    }

    /**
     * increment initial state's "block-counter" value by $step
     *
     * @param uint32    $step       step added to current block-counter value
     */
    public function inc_counter(int $step = 1) /* add ': void' in php 7.1 */ {
        $curval = $this->initial_state[self::STATE_COUNTER_BASEINDEX];
        $newval = $this->cap($curval + $step);
        $this->set_counter($newval);
    }

    /**
     * puts binary data to internal state
     *
     * extract $num little-endian uint32 from a least-significant-bit-starting
     * BINARY str named $name, and places these uint32's into internal-state
     * starting at $index
     *
     * @param   binary_string     $str    binary string holding little-endian uint32's
     */
    public function bin_to_internal(string $str, string $name, int $index, int $num) /* add ': void' in php 7.1 */ {
        if ($index < 0) {
            throw new ChaCha20Exception(sprintf("Index %d cannot be negative", $index));
        }
        if ($num < 0) {
            throw new ChaCha20Exception(sprintf("Amount %d cannot be negative", $num));
        }
        if ($index + $num > self::STATE_ARRAY_LENGTH) {
            throw new ChaCha20Exception(sprintf("Cannot copy %d numbers starting at index %d as it would exceed target size of %d", $num, $index, self::STATE_ARRAY_LENGTH));
        }
        // check for input length
        $req_len = $num * self::INT_BIT_LENGTH >> 3;
        if (strlen($str) !== $req_len) {
            throw new ChaCha20Exception(sprintf('%s "%s" is not a %d-bits long hex string', $name, bin2hex($str), $req_len));
        }
        // extract littl-endian uint32 from it
        $arr = unpack("V".$num, $str);
        // place nonce uint32 into the state
        array_splice($this->initial_state, $index, $num, $arr);
    }

    /**
     * set key from a least-significant-bit-starting BINARY string
     */
    public function set_key(string $string) /* add ': void' in php 7.1 */ {
        $this->bin_to_internal(
            $string,
            "Key",
            self::STATE_KEY_BASEINDEX,
            self::STATE_KEY_LENGTH);
    }

    /**
     * set nonce from a least-significant-bit-starting BINARY string
     */
    public function set_nonce(string $string) /* add ': void' in php 7.1 */ {
        $this->bin_to_internal(
            $string,
            "Nonce",
            self::STATE_NONCE_BASEINDEX,
            self::STATE_NONCE_LENGTH);
    }

    /**
     * display internal state in matrix form
     */
    public function __toString() : string {
        return vsprintf("00:0x%08x\t01:0x%08x\t02:0x%08x\t03:0x%08x\n04:0x%08x\t05:0x%08x\t06:0x%08x\t07:0x%08x\n08:0x%08x\t09:0x%08x\t10:0x%08x\t11:0x%08x\n12:0x%08x\t13:0x%08x\t14:0x%08x\t15:0x%08x\n", $this->final_state);
    }

    /**
     * apply a quarter-round to internal-state
     */
    public function do_quarter_round(int $i_a, int $i_b, int $i_c, int $i_d) /* add ': void' in php 7.1 */ {
        // fetch required uint32's
        $a = $this->final_state[$i_a];
        $b = $this->final_state[$i_b];
        $c = $this->final_state[$i_c];
        $d = $this->final_state[$i_d];
        // do the quarter round
        $a = $this->add_cap($a, $b);  // a += b;
        $d = $this->xor($d, $a);      // d ^= a;
        $d = $this->rot_left($d, 16); // d <<<= 16;
        $c = $this->add_cap($c, $d);  // c += d;
        $b = $this->xor($b, $c);      // b ^= c;
        $b = $this->rot_left($b, 12); // b <<<= 12;
        $a = $this->add_cap($a, $b);  // a += b;
        $d = $this->xor($d, $a);      // d ^= a;
        $d = $this->rot_left($d, 8);  // d <<<= 8;
        $c = $this->add_cap($c, $d);  // c += d;
        $b = $this->xor($b, $c);      // b ^= c;
        $b = $this->rot_left($b, 7);  // b <<<= 7;
        // stores modified uint32's
        $this->final_state[$i_a] = $a;
        $this->final_state[$i_b] = $b;
        $this->final_state[$i_c] = $c;
        $this->final_state[$i_d] = $d;
    }

    /**
     * computes a block
     */
    public function compute_block() /* add ': void' in php 7.1 */ {
        // initialize internal state with algorithm constants
        $this->set_const_index_value(0, self::CONSTANT_VALUE_0);
        $this->set_const_index_value(1, self::CONSTANT_VALUE_1);
        $this->set_const_index_value(2, self::CONSTANT_VALUE_2);
        $this->set_const_index_value(3, self::CONSTANT_VALUE_3);
        // start from the initial state
        $this->final_state = $this->initial_state;
        // compute full rounds
        for ($i=0; $i<self::FULL_QUARTER_ROUND_ITERATIONS; $i++) {
            // column rounds
            $this->do_quarter_round(0, 4, 8,12); // 1st column
            $this->do_quarter_round(1, 5, 9,13); // 2nd column
            $this->do_quarter_round(2, 6,10,14); // 3rd column
            $this->do_quarter_round(3, 7,11,15); // 4th column
            // diagonal rounds
            $this->do_quarter_round(0, 5,10,15); // 1st diagonal
            $this->do_quarter_round(1, 6,11,12); // 2nd diagonal
            $this->do_quarter_round(2, 7, 8,13); // 3rd diagonal
            $this->do_quarter_round(3, 4, 9,14); // 4th diagonal
        }
        // add the initial state to the final state
        for ($i=0; $i<self::STATE_ARRAY_LENGTH; $i++) {
            $this->final_state[$i] = $this->add_cap($this->final_state[$i], $this->initial_state[$i]);
        }
    }

    /**
     * get internal state as an array of uint32
     *
     * @param int    $state      enum defining which state to return
     */
    public function get_state(int $state) /* add ': void' in php 7.1 */ {
        switch ($state) {
            case self::STATE_INTERNAL:
                return $this->initial_state;
            case self::STATE_PRE_FINAL:
                return $this->intermediary_state;
            case self::STATE_FINAL:
                return $this->final_state;
            default:
                throw new ChaCha20Exception(sprintf("State enum %d is invalid", $state));
        }
    }

    /**
     * construct a "NULL" Block
     *
     * creates and initalize a Block.
     *
     */
    public function __construct(string $key=NULL, string $nonce=NULL, string $ctr=NULL) {
        // initialize
        $this->initial_state = array_fill(0, self::STATE_ARRAY_LENGTH, 0x00000000);
        $this->final_state = $this->initial_state;
    }
}
