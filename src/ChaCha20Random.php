<?php

declare(strict_types=1);

namespace WRS;

/**
 * Generates PRNG, providing uint32 by using a ChaCha20 blocks
 */
class ChaCha20Random extends ChaCha20Block {

    /**
     * sub_counter is the integer index in the current block
     **/

    private $sub_counter;

    public function set_sub_counter(int $index) {
        if ($index < 0 or $index >= self::STATE_KEY_LENGTH) {
            throw new ChaCha20Exception(sprintf("Sub-counter index %d is outstide range [0..%d[", $index, self::STATE_KEY_LENGTH.'['));
        }
        $this->sub_counter = $index;
    }

    public function get_sub_counter() {
        return $this->sub_counter;
    }


    /**
     * generates a key from /dev/urandom
     */
    public static function weak_random_key() {
        return $str = random_bytes(
            ChaCha20Block::STATE_KEY_LENGTH
            * ChaCha20Block::INT_BIT_LENGTH
            >> 3);
    }

    /**
     * generates a nonce from /dev/urandom
     */
    public static function weak_random_nonce() {
        return random_bytes(
            ChaCha20Block::STATE_NONCE_LENGTH
            * ChaCha20Block::INT_BIT_LENGTH
            >> 3);
    }

    /**
     * generates a counter from /dev/urandom
     */
    public static function weak_random_counter() {
        $str = random_bytes(
            ChaCha20Block::INT_BIT_LENGTH
            >> 3);
        return unpack("V1", $str)[1];
    }

    /**
     * generates a sub-counter from /dev/urandom
     */
    public static function weak_random_sub_counter() {
        $str = random_bytes(1);
        $value = ord($str[0]) % ChaCha20Block::STATE_ARRAY_LENGTH;
        return $value;
    }

    /**
     * generates an PRNG object (possibly randomized)
     */
    public function __construct(string $key=NULL, string $nonce=NULL, int $block_ctr=NULL, $block_sub_ctr = NULL) {
        // provide random if necessary
        if ($key === NULL) {
            $key = self::weak_random_key();
        }
        if ($nonce === NULL) {
            $nonce = self::weak_random_nonce();
        }
        if ($block_ctr === NULL) {
            $block_ctr = self::weak_random_counter();
        }
        if ($block_sub_ctr === NULL) {
            $block_sub_ctr = self::weak_random_sub_counter();
        }
        // initialize ChaCha20Block
        parent::__construct($key, $nonce, $block_ctr);
        // initialize state index
        $block_sub_index = $block_sub_ctr;
        // compute first block of data
        $this->compute_block();
    }
}
