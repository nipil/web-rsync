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
     * Number of full (column + diagonal) iterations
     */
    const FULL_QUARTER_ROUND_ITERATIONS = 10;

    /**
     * internal state is what is built when key, nonce, ctr are modified
     */
    private $initial_state;

    /**
     * final state is the output of the calculation
     */
    private $final_state;

    /**
     * ensures that every manipulated value is truncated to integer length
     *
     * @return uint32    an integer capped at INT_BIT_MASK bits
     */
    protected function cap($value) {
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
    protected function rot_left($value, $left) {
        if ($left < 0 or $left >= self::INT_BIT_LENGTH) {
            throw new Exception(sprintf("Left bitwise-rotation %d is outstide range [0..%d[", $left, self::INT_BIT_LENGTH));
        }
        $first = $value;
        $lp = $this->cap($value << $left);
        $rp = $this->cap($value >> (self::INT_BIT_LENGTH - $left));
        $value = $lp | $rp;
        return $value;
    }


    /**
     * does a bitwise XOR between the arguments
     *
     * @return uint32    an integer capped at INT_BIT_MASK bits
     */
    protected function xor($a, $b) {
        printf("A) 0x%08x\t%s\t%d\n", $a, str_pad(decbin($a), 32, '0', STR_PAD_LEFT), $a);
        printf("B) 0x%08x\t%s\t%d\n", $b, str_pad(decbin($b), 32, '0', STR_PAD_LEFT), $b);
        $x = $a ^ $b;
        printf("X) 0x%08x\t%s\t%d\n", $x, str_pad(decbin($x), 32, '0', STR_PAD_LEFT), $x);
        return $x;
    }

    /**
     * add two integers and cap the sum to the required number of bits
     *
     * @return uint32    an integer capped at INT_BIT_MASK bits
     */
    protected function add_cap($a, $b) {
        return $this->cap($a + $b);
    }

    /**
     * initialize internal "const" values one by one
     *
     * @param int       $index      index in const range
     * @param uint32    $value      value to store
     */
    private function set_const($index, $value) {
        if ($index < 0 or $index >= self::STATE_CONST_LENGTH) {
            throw new Exception(sprintf("Const index %d is outstide range [0..%d[", $index, self::STATE_CONST_LENGTH.'['));
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
            throw new Exception(sprintf("Key index %d is outstide range [0..%d[", $index, self::STATE_KEY_LENGTH.'['));
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
            throw new Exception(sprintf("Nonce index %d is outstide range [0..%d[", $index, self::STATE_NONCE_LENGTH.'['));
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
     * BINARY str named $name, and places these uint32's into internal-state
     * starting at $index
     *
     * @param   binary_string     $str    binary string holding little-endian uint32's
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
     * apply a quarter-round to internal-state
     */
    public function do_quarter_round($i_a, $i_b, $i_c, $i_d) {
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
    public function compute_block() {
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
