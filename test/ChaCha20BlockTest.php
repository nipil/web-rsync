<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WRS\ChaCha20Block;
use WRS\ChaCha20Exception;

/**
 * @covers ChaCha20BlockTest
 */
final class ChaCha20BlockTest extends TestCase
{

    public function providerAddCap() {
        return [
            "no-overflow" => [
                0x77777777,
                0x01234567,
                0x789abcde],
            "signed-overflow" => [
                0x77767777,
                0x11239567,
                ChaCha20Block::buildUint32(0x889a, 0x0cde)],
            "unsigned-overflow" => [
                ChaCha20Block::buildUint32(0x8776, 0x8777),
                ChaCha20Block::buildUint32(0x8123, 0x8567),
                ChaCha20Block::buildUint32(0x089a, 0x0cde)],
        ];
    }

    /**
     * @dataProvider providerAddCap
     */
    public function testAddCap(int $a, int $b, int $expected) /* add ': void' in php 7.1 */
    {
            $this->assertEquals($expected, ChaCha20Block::add_cap($a, $b));
    }

    public function testCap() /* add ': void' in php 7.1 */
    {
        if (PHP_INT_SIZE === 4) {
            $this->assertTrue(TRUE);
        } else {
            $this->assertEquals(0xFFFFFFFF, ChaCha20Block::cap(0x7FFFFFFFFFFFFFFF));
        }
    }

    public function testRotLeft() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.1
        $this->assertEquals(
            ChaCha20Block::buildUint32(0xcc5f, 0xed3c),
            ChaCha20Block::rot_left(0x7998bfda, 7));
    }

    public function testXor() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.1
        $this->assertEquals(0x7998bfda, ChaCha20Block::xor(0x01020304, 0x789abcde));
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
        // rfc7539 test vector 2.2.1

        $vector = [
            ChaCha20Block::buildUint32(0x8795, 0x31e0),
            ChaCha20Block::buildUint32(0xc5ec, 0xf37d),
            0x516461b1, // 2
            ChaCha20Block::buildUint32(0xc9a6, 0x2f8a),
            0x44c20ef3,
            0x3390af7f,
            ChaCha20Block::buildUint32(0xd9fc, 0x690b),
            0x2a5f714c, // 7
            0x53372767, // 8
            ChaCha20Block::buildUint32(0xb00a, 0x5631),
            ChaCha20Block::buildUint32(0x974c, 0x541a),
            0x359e9963,
            0x5c971061,
            0x3d631689, // 13
            0x2098d9d6,
            ChaCha20Block::buildUint32(0x91db, 0xd320)
        ];

        ChaCha20Block::do_quarter_round(2, 7, 8, 13, $vector);

        $this->assertEquals([
                ChaCha20Block::buildUint32(0x8795, 0x31e0),
                ChaCha20Block::buildUint32(0xc5ec, 0xf37d),
                ChaCha20Block::buildUint32(0xbdb8, 0x86dc), // 2
                ChaCha20Block::buildUint32(0xc9a6, 0x2f8a),
                0x44c20ef3,
                0x3390af7f,
                ChaCha20Block::buildUint32(0xd9fc, 0x690b),
                ChaCha20Block::buildUint32(0xcfac, 0xafd2), // 7
                ChaCha20Block::buildUint32(0xe46b, 0xea80), // 8
                ChaCha20Block::buildUint32(0xb00a, 0x5631),
                ChaCha20Block::buildUint32(0x974c, 0x541a),
                0x359e9963,
                0x5c971061,
                ChaCha20Block::buildUint32(0xccc0, 0x7c79), // 13
                0x2098d9d6,
                ChaCha20Block::buildUint32(0x91db, 0xd320)
            ],
            $vector);
    }

    public function testConstructorEmpty() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.3.2
        $c = new ChaCha20Block();

        // initial
        $this->assertEquals([
                0x61707865, 0x3320646e, 0x79622d32, 0x6b206574,
                0x00000000, 0x00000000, 0x00000000, 0x00000000,
                0x00000000, 0x00000000, 0x00000000, 0x00000000,
                0x00000000, 0x00000000, 0x00000000, 0x00000000
            ],
            $c->get_state(ChaCha20Block::STATE_INITIAL),
            "clear state failed");

        // check counter
        $this->assertEquals(0, $c->get_counter());
    }

    public function testConstructorValued() /* add ': void' in php 7.1 */
    {
        // rfc7539 test vector 2.3.2
        $key = "000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f";
        $nonce = "000000090000004a00000000";
        $ctr = 1;

        // valued constructor
        $c = new ChaCha20Block(hex2bin($key), hex2bin($nonce), $ctr);

        // initial
        $this->assertEquals([
                0x61707865, 0x3320646e, 0x79622d32, 0x6b206574,
                0x03020100, 0x07060504, 0x0b0a0908, 0x0f0e0d0c,
                0x13121110, 0x17161514, 0x1b1a1918, 0x1f1e1d1c,
                0x00000001, 0x09000000, 0x4a000000, 0x00000000
            ],
            $c->get_state(ChaCha20Block::STATE_INITIAL),
            "initial state failed");

        // check counter
        $this->assertEquals(1, $c->get_counter());

        // provides
        return $c;
    }

    /**
     * @depends testConstructorValued
     */
    public function testComputeBlock($c) /* add ': void' in php 7.1 */
    {
        // compute
        $c->compute_block();

        // intermediate
        $this->assertEquals([
                ChaCha20Block::buildUint32(0x8377, 0x78ab),
                ChaCha20Block::buildUint32(0xe238, 0xd763),
                0xa67ae21e,
                0x5950bb2f,
                ChaCha20Block::buildUint32(0xc4f2, 0xd0c7),
                ChaCha20Block::buildUint32(0xfc62, 0xbb2f),
                ChaCha20Block::buildUint32(0x8fa0, 0x18fc),
                0x3f5ec7b7,
                0x335271c2,
                ChaCha20Block::buildUint32(0xf294, 0x89f3),
                ChaCha20Block::buildUint32(0xeabd, 0xa8fc),
                ChaCha20Block::buildUint32(0x82e4, 0x6ebd),
                ChaCha20Block::buildUint32(0xd19c, 0x12b4),
                ChaCha20Block::buildUint32(0xb04e, 0x16de),
                ChaCha20Block::buildUint32(0x9e83, 0xd0cb),
                0x4e3c50a2
            ],
            $c->get_state(ChaCha20Block::STATE_INTERMEDIATE),
            "intermediate state failed");

        // final
        $this->assertEquals([
                ChaCha20Block::buildUint32(0xe4e7, 0xf110),
                0x15593bd1,
                0x1fdd0f50,
                ChaCha20Block::buildUint32(0xc471, 0x20a3),
                ChaCha20Block::buildUint32(0xc7f4, 0xd1c7),
                0x0368c033,
                ChaCha20Block::buildUint32(0x9aaa, 0x2204),
                0x4e6cd4c3,
                0x466482d2,
                0x09aa9f07,
                0x05d7c214,
                ChaCha20Block::buildUint32(0xa202, 0x8bd9),
                ChaCha20Block::buildUint32(0xd19c, 0x12b5),
                ChaCha20Block::buildUint32(0xb94e, 0x16de),
                ChaCha20Block::buildUint32(0xe883, 0xd0cb),
                0x4e3c50a2
            ],
            $c->get_state(ChaCha20Block::STATE_FINAL),
            "final state failed");

        // serialize
        $this->assertEquals(
            "10f1e7e4d13b5915500fdd1fa32071c4c7d1f4c733c068030422aa9ac3d46c4ed2826446079faa0914c2d705d98b02a2b5129cd1de164eb9cbd083e8a2503c4e",
            bin2hex($c->serialize_state(ChaCha20Block::STATE_FINAL)),
            "serialize failed");
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionRotate() /* add ': void' in php 7.1 */
    {
        ChaCha20Block::rot_left(0, -1);
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionSetConstIndexValue() /* add ': void' in php 7.1 */
    {
        $c = new ChaCha20Block();
        $c->set_const_index_value(-1, 0);
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionSetKeyIndexValue() /* add ': void' in php 7.1 */
    {
        $c = new ChaCha20Block();
        $c->set_key_index_value(-1, 0);
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionSetNonceIndexValue() /* add ': void' in php 7.1 */
    {
        $c = new ChaCha20Block();
        $c->set_nonce_index_value(-1, 0);
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionBinToInternalTooLong() /* add ': void' in php 7.1 */
    {
        $c = new ChaCha20Block();
        $c->bin_to_initial("toolong", "test", 0, 0);
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionBinToInternalInvalidIndex() /* add ': void' in php 7.1 */
    {
        $c = new ChaCha20Block();
        $c->bin_to_initial("", "test", -1, 0);
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionBinToInternalInvalidLength() /* add ': void' in php 7.1 */
    {
        $c = new ChaCha20Block();
        $c->bin_to_initial("", "test", 0, -1);
    }

    /**
     * @expectedException WRS\ChaCha20Exception
     */
    public function testExceptionBinToInternalTotalTooLong() /* add ': void' in php 7.1 */
    {
        $c = new ChaCha20Block();
        $c->bin_to_initial(
            "12345678901234567890123456789012",
            "test",
            0,
            ChaCha20Block::STATE_ARRAY_LENGTH + 1);
    }
}
