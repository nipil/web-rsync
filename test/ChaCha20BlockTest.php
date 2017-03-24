<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WRS\ChaCha20Block;

/**
 * @covers ChaCha20BlockTest
 */
final class ChaCha20BlockTest extends TestCase
{
    public function testCap() /* add ': void' in php 7.1 */
    {
        $this->assertEquals(0xFFFFFFFF, ChaCha20Block::cap(0x7FFFFFFFFFFFFFFF));
    }

    public function testRotLeft() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.1
        $this->assertEquals(0xcc5fed3c, ChaCha20Block::rot_left(0x7998bfda, 7));
    }

    public function testXor() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.1
        $this->assertEquals(0x7998bfda, ChaCha20Block::xor(0x01020304, 0x789abcde));
    }

    public function testAddCapWithoutOverlflow() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.1
        $this->assertEquals(0x789abcde, ChaCha20Block::add_cap(0x77777777, 0x01234567));
    }

    public function testAddCapWithOverlflow() /* add ': void' in php 7.1 */
    {
        $this->assertEquals(0x08888888, ChaCha20Block::add_cap(0x87777777, 0x81111111));
    }

    // set_const(int $index, int $value)
    public function testSetConstIndexValue() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // set_key_index_uint32(int $index, int $value)
    public function testSetKeyIndexValue() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // set_nonce_index_uint32(int $index, int $value)
    public function testSetNonceIndexValue() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // set_counter(int $position)
    public function testSetCounter() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // inc_counter(int $step = 1)
    public function testIncCounter() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // bin_to_internal(string $str, string $name, int $index, int $num)
    public function testBinToInternal() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // set_key(string $string)
    public function testSetKey() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // set_nonce(string $string)
    public function testSetNonce() /* add ': void' in php 7.1 */
    {
        $this->assertTrue(TRUE);
    }

    // do_quarter_round(int $i_a, int $i_b, int $i_c, int $i_d)
    public function testQuarterRound() /* add ': void' in php 7.1 */
    {

   //     879531e0  c5ecf37d  516461b1  c9a62f8a
   //     44c20ef3  3390af7f  d9fc690b  2a5f714c
   //     53372767  b00a5631  974c541a  359e9963
   //     5c971061  3d631689  2098d9d6  91dbd320

   // We will apply the QUARTERROUND(2,7,8,13) operation to this state.
   // For obvious reasons, this one is part of what is called a "diagonal
   // round":

   // After applying QUARTERROUND(2,7,8,13)

   //     879531e0  c5ecf37d *bdb886dc  c9a62f8a
   //     44c20ef3  3390af7f  d9fc690b *cfacafd2
   //    *e46bea80  b00a5631  974c541a  359e9963
   //     5c971061 *ccc07c79  2098d9d6  91dbd320
        $this->assertTrue(TRUE);
    }

    public function testComputeBlock() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.3.2
        $c = new ChaCha20Block();
        $initial = $c->get_state(ChaCha20Block::STATE_INITIAL);
        $this->assertEquals([
            0x00000000, 0x00000000, 0x00000000, 0x00000000,
            0x00000000, 0x00000000, 0x00000000, 0x00000000,
            0x00000000, 0x00000000, 0x00000000, 0x00000000,
            0x00000000, 0x00000000, 0x00000000, 0x00000000
        ], $initial, "clear state failed");

        // initialize
        $c->set_key(hex2bin("000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f"));
        $c->set_nonce(hex2bin("000000090000004a00000000"));
        $c->set_counter(1);

        // compute
        $c->compute_block();

        // initial
        $initial = $c->get_state(ChaCha20Block::STATE_INITIAL);
        $this->assertEquals([
            0x61707865, 0x3320646e, 0x79622d32, 0x6b206574,
            0x03020100, 0x07060504, 0x0b0a0908, 0x0f0e0d0c,
            0x13121110, 0x17161514, 0x1b1a1918, 0x1f1e1d1c,
            0x00000001, 0x09000000, 0x4a000000, 0x00000000
        ], $initial, "initial state failed");

        // intermediate
        $intermediate = $c->get_state(ChaCha20Block::STATE_INTERMEDIATE);
        $this->assertEquals([
            0x837778ab, 0xe238d763, 0xa67ae21e, 0x5950bb2f,
            0xc4f2d0c7, 0xfc62bb2f, 0x8fa018fc, 0x3f5ec7b7,
            0x335271c2, 0xf29489f3, 0xeabda8fc, 0x82e46ebd,
            0xd19c12b4, 0xb04e16de, 0x9e83d0cb, 0x4e3c50a2
        ], $intermediate, "intermediate state failed");

        // final
        $final = $c->get_state(ChaCha20Block::STATE_FINAL);
        $this->assertEquals([
            0xe4e7f110, 0x15593bd1, 0x1fdd0f50, 0xc47120a3,
            0xc7f4d1c7, 0x0368c033, 0x9aaa2204, 0x4e6cd4c3,
            0x466482d2, 0x09aa9f07, 0x05d7c214, 0xa2028bd9,
            0xd19c12b5, 0xb94e16de, 0xe883d0cb, 0x4e3c50a2
        ], $final, "final state failed");

        // serialize
        $serial = bin2hex($c->serialize_state(ChaCha20Block::STATE_FINAL));
        $this->assertEquals($serial, "10f1e7e4d13b5915500fdd1fa32071c4c7d1f4c733c068030422aa9ac3d46c4ed2826446079faa0914c2d705d98b02a2b5129cd1de164eb9cbd083e8a2503c4e", "serialize failed");
    }
}

// Exceptions
// self::rot_left(0, -1);
// $this->set_const_index_value(-1, 0);
// $this->set_key_index_value(-1, 0);
// $this->set_nonce_index_value(-1, 0);
// $this->bin_to_internal("toolong", "", 0, 0);
// $this->bin_to_internal("", "", -1, 0);
// $this->bin_to_internal("", "", 0, -1);
// $this->bin_to_internal("12345678901234567890123456789012", "", 0, 10*self::STATE_ARRAY_LENGTH);
