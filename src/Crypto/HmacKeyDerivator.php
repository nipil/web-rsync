<?php

declare(strict_types=1);

namespace WRS\Crypto;

use WRS\Crypto\Interfaces\HashInterface;
use WRS\Crypto\Interfaces\KeyDerivatorInterface;
use WRS\Crypto\Interfaces\SecretKeeperInterface;

class HmacKeyDerivator implements KeyDerivatorInterface
{
    const MAX_ITERATIONS = 0xFF;

    private $secret;
    private $hasher;
    private $prk;

    public function __construct(SecretKeeperInterface $secret, HashInterface $hasher)
    {
        $this->secret = $secret;
        $this->hasher = $hasher;
        $this->prk = null;
    }

    public function getPrk()
    {
        return $this->prk;
    }

    public function deriveKey(int $byte_length, string $info)
    {

        // reject negative byte_length
        if ($byte_length < 0) {
            throw new \Exception(sprintf("Invalid key length : %d", $byte_length));
        }

        // quickly return on simple edge case
        if ($byte_length === 0) {
            return "";
        }

        /*
         * extract phase
         *
         * RFC 5869 2.1 note :
         *     'IKM' is used as the HMAC input, not as the HMAC key
         *
         * So in this extract phase :
         * - secret key  is hmac_message
         * - secret salt is hmac_key
         */
        $this->prk = $this->hasher->hmac(
            $this->secret->getKey(),  // hmac_message
            $this->secret->getSalt()  // hmac_key
        );

        /*
         * handles variable hashing functions
         */
        $hash_len = strlen($this->prk);
        $iterations = ceil($byte_length / $hash_len);

        /*
         * RFC 5869 section 2.3 specifies that the added
         * counter is a single byte, thus we check for it
         */
        if ($iterations > self::MAX_ITERATIONS) {
            throw new \Exception(sprintf("Too many iterations required : %d", $iterations));
        }

        /*
         * expand phase
         */
        $buffer = "";
        $iteration_output = "";
        for ($i = 1; $i <= $iterations; $i++) {
            $iteration_input = $iteration_output . $info . chr($i);
            $iteration_output = $this->hasher->hmac($iteration_input, $this->prk);
            $buffer .= $iteration_output;
        }

        // retain only requested byte_length bytes
        return substr($buffer, 0, $byte_length);
    }
}
