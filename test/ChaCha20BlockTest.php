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
                ChaCha20Block::buildUint32(0x8666, 0x6666),
                ChaCha20Block::buildUint32(0x8111, 0x1111),
                ChaCha20Block::buildUint32(0x0777, 0x7777)],
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

    public function providerRotLeft()
    {
        return [

            // rfc7539 test vector 2.1
            [0x7998bfda, 7, ChaCha20Block::buildUint32(0xcc5f, 0xed3c)],

            // failed at first on 32bit because >> pulled sign bits
            [ChaCha20Block::buildUint32(0xa59f, 0X595f), 7, ChaCha20Block::buildUint32(0xcfac, 0xafd2)],

            /**
             * following the discovery of the above failed test, the below set has been generated
             * the following dataset has been generated using assembly ROL instruction, via the following C program :
             *
             * #include <stdlib.h>
             * #include <stdio.h>
             *
             * int32_t getrand() {
             *    int32_t h = rand() & 0xFFFF;
             *    int32_t l = rand() & 0xFFFF;
             *    return h << 16 | l;
             * }
             *
             * void rotate(int32_t value, int8_t left) {
             *    printf("[ChaCha20Block::buildUint32(0x%04x, 0x%04x), ", (value >> 16) & 0xFFFF, value & 0xFFFF);
             *    printf("%d, ", left);
             *    asm("roll %1,%0" : "+r" (value) : "c" (left));
             *    printf("ChaCha20Block::buildUint32(0x%04x, 0x%04x)],\n", (value >> 16) & 0xFFFF, value & 0xFFFF);
             * }
             *
             * int main(void) {
             *    int i = 0;
             *    time_t t;
             *    int32_t v;
             *    int8_t w;
             *    srand((unsigned) time(&t));
             *    for (i=0; i<100; i++) {
             *        v = getrand();
             *        w = abs(getrand()) % 32;
             *        rotate(v, w);
             *    }
             *    rotate(0x7998bfda, 7); // 0xcc5fed3c
             *    rotate(0xa59f595f, 7); // 0xcfacafd2
             * }
             */
            [ChaCha20Block::buildUint32(0x34a9, 0x0cbe),  18, ChaCha20Block::buildUint32(0x32f8, 0xd2a4)],
            [ChaCha20Block::buildUint32(0xb021, 0x4af7),  9, ChaCha20Block::buildUint32(0x4295, 0xef60)],
            [ChaCha20Block::buildUint32(0x4618, 0x8198),  25, ChaCha20Block::buildUint32(0x308c, 0x3103)],
            [ChaCha20Block::buildUint32(0x6150, 0xb4af),  9, ChaCha20Block::buildUint32(0xa169, 0x5ec2)],
            [ChaCha20Block::buildUint32(0xd526, 0x6d45),  12, ChaCha20Block::buildUint32(0x66d4, 0x5d52)],
            [ChaCha20Block::buildUint32(0x3c61, 0xd6f1),  3, ChaCha20Block::buildUint32(0xe30e, 0xb789)],
            [ChaCha20Block::buildUint32(0x3739, 0xface),  4, ChaCha20Block::buildUint32(0x739f, 0xace3)],
            [ChaCha20Block::buildUint32(0x73c1, 0x30c5),  10, ChaCha20Block::buildUint32(0x04c3, 0x15cf)],
            [ChaCha20Block::buildUint32(0x3d83, 0xf78d),  4, ChaCha20Block::buildUint32(0xd83f, 0x78d3)],
            [ChaCha20Block::buildUint32(0x4285, 0xb493),  3, ChaCha20Block::buildUint32(0x142d, 0xa49a)],
            [ChaCha20Block::buildUint32(0x362b, 0xd5b6),  4, ChaCha20Block::buildUint32(0x62bd, 0x5b63)],
            [ChaCha20Block::buildUint32(0x8a65, 0xe663),  21, ChaCha20Block::buildUint32(0xcc71, 0x4cbc)],
            [ChaCha20Block::buildUint32(0x53a8, 0xbb70),  23, ChaCha20Block::buildUint32(0xb829, 0xd45d)],
            [ChaCha20Block::buildUint32(0x9261, 0xd684),  6, ChaCha20Block::buildUint32(0x9875, 0xa124)],
            [ChaCha20Block::buildUint32(0xd152, 0x1d66),  19, ChaCha20Block::buildUint32(0xeb36, 0x8a90)],
            [ChaCha20Block::buildUint32(0x4e2b, 0xce89),  18, ChaCha20Block::buildUint32(0x3a25, 0x38af)],
            [ChaCha20Block::buildUint32(0xc617, 0x5e3a),  28, ChaCha20Block::buildUint32(0xac61, 0x75e3)],
            [ChaCha20Block::buildUint32(0x12ce, 0x4a40),  7, ChaCha20Block::buildUint32(0x6725, 0x2009)],
            [ChaCha20Block::buildUint32(0x1ff6, 0x7ad0),  4, ChaCha20Block::buildUint32(0xff67, 0xad01)],
            [ChaCha20Block::buildUint32(0x6133, 0x7949),  27, ChaCha20Block::buildUint32(0x4b09, 0x9bca)],
            [ChaCha20Block::buildUint32(0x34b9, 0xcbbf),  26, ChaCha20Block::buildUint32(0xfcd2, 0xe72e)],
            [ChaCha20Block::buildUint32(0xa243, 0x1bd2),  11, ChaCha20Block::buildUint32(0x18de, 0x9512)],
            [ChaCha20Block::buildUint32(0x3938, 0xe4b2),  29, ChaCha20Block::buildUint32(0x4727, 0x1c96)],
            [ChaCha20Block::buildUint32(0xb33b, 0xa626),  18, ChaCha20Block::buildUint32(0x989a, 0xccee)],
            [ChaCha20Block::buildUint32(0x0461, 0x8c63),  17, ChaCha20Block::buildUint32(0x18c6, 0x08c3)],
            [ChaCha20Block::buildUint32(0xd6a4, 0x1328),  26, ChaCha20Block::buildUint32(0xa35a, 0x904c)],
            [ChaCha20Block::buildUint32(0x8df8, 0x409e),  21, ChaCha20Block::buildUint32(0x13d1, 0xbf08)],
            [ChaCha20Block::buildUint32(0xb9e7, 0xaade),  0, ChaCha20Block::buildUint32(0xb9e7, 0xaade)],
            [ChaCha20Block::buildUint32(0x769d, 0xe8eb),  0, ChaCha20Block::buildUint32(0x769d, 0xe8eb)],
            [ChaCha20Block::buildUint32(0x04bd, 0x466e),  11, ChaCha20Block::buildUint32(0xea33, 0x7025)],
            [ChaCha20Block::buildUint32(0x2b20, 0x451d),  5, ChaCha20Block::buildUint32(0x6408, 0xa3a5)],
            [ChaCha20Block::buildUint32(0xeb44, 0xd869),  5, ChaCha20Block::buildUint32(0x689b, 0x0d3d)],
            [ChaCha20Block::buildUint32(0x64cd, 0xd99c),  17, ChaCha20Block::buildUint32(0xb338, 0xc99b)],
            [ChaCha20Block::buildUint32(0xecc4, 0x66fc),  28, ChaCha20Block::buildUint32(0xcecc, 0x466f)],
            [ChaCha20Block::buildUint32(0xa79a, 0xd302),  1, ChaCha20Block::buildUint32(0x4f35, 0xa605)],
            [ChaCha20Block::buildUint32(0x7de0, 0x0dee),  29, ChaCha20Block::buildUint32(0xcfbc, 0x01bd)],
            [ChaCha20Block::buildUint32(0xf6d9, 0x05db),  23, ChaCha20Block::buildUint32(0xedfb, 0x6c82)],
            [ChaCha20Block::buildUint32(0x4c49, 0x99d2),  9, ChaCha20Block::buildUint32(0x9333, 0xa498)],
            [ChaCha20Block::buildUint32(0xdeef, 0xfee5),  19, ChaCha20Block::buildUint32(0xf72e, 0xf77f)],
            [ChaCha20Block::buildUint32(0xd74e, 0xad73),  5, ChaCha20Block::buildUint32(0xe9d5, 0xae7a)],
            [ChaCha20Block::buildUint32(0x870f, 0xc0ac),  20, ChaCha20Block::buildUint32(0x0ac8, 0x70fc)],
            [ChaCha20Block::buildUint32(0x27a9, 0xa998),  29, ChaCha20Block::buildUint32(0x04f5, 0x3533)],
            [ChaCha20Block::buildUint32(0x7c9a, 0x5878),  26, ChaCha20Block::buildUint32(0xe1f2, 0x6961)],
            [ChaCha20Block::buildUint32(0x6666, 0x80e6),  1, ChaCha20Block::buildUint32(0xcccd, 0x01cc)],
            [ChaCha20Block::buildUint32(0x86c2, 0xfc54),  11, ChaCha20Block::buildUint32(0x17e2, 0xa436)],
            [ChaCha20Block::buildUint32(0x9626, 0x9263),  21, ChaCha20Block::buildUint32(0x4c72, 0xc4d2)],
            [ChaCha20Block::buildUint32(0x9148, 0xa03a),  22, ChaCha20Block::buildUint32(0x0ea4, 0x5228)],
            [ChaCha20Block::buildUint32(0x4dad, 0xf921),  4, ChaCha20Block::buildUint32(0xdadf, 0x9214)],
            [ChaCha20Block::buildUint32(0xb9ce, 0x1c3e),  23, ChaCha20Block::buildUint32(0x1f5c, 0xe70e)],
            [ChaCha20Block::buildUint32(0xc5d6, 0x3721),  16, ChaCha20Block::buildUint32(0x3721, 0xc5d6)],
            [ChaCha20Block::buildUint32(0x8f99, 0xe17f),  31, ChaCha20Block::buildUint32(0xc7cc, 0xf0bf)],
            [ChaCha20Block::buildUint32(0x6266, 0x2be1),  8, ChaCha20Block::buildUint32(0x662b, 0xe162)],
            [ChaCha20Block::buildUint32(0x2835, 0xac15),  5, ChaCha20Block::buildUint32(0x06b5, 0x82a5)],
            [ChaCha20Block::buildUint32(0x3e78, 0x06a8),  0, ChaCha20Block::buildUint32(0x3e78, 0x06a8)],
            [ChaCha20Block::buildUint32(0xa6e2, 0x72ba),  15, ChaCha20Block::buildUint32(0x395d, 0x5371)],
            [ChaCha20Block::buildUint32(0x6bdb, 0xdd08),  23, ChaCha20Block::buildUint32(0x8435, 0xedee)],
            [ChaCha20Block::buildUint32(0xf947, 0x11dc),  29, ChaCha20Block::buildUint32(0x9f28, 0xe23b)],
            [ChaCha20Block::buildUint32(0x48fd, 0xb7db),  22, ChaCha20Block::buildUint32(0xf6d2, 0x3f6d)],
            [ChaCha20Block::buildUint32(0x995a, 0x3e78),  0, ChaCha20Block::buildUint32(0x995a, 0x3e78)],
            [ChaCha20Block::buildUint32(0x6a5a, 0x21d4),  17, ChaCha20Block::buildUint32(0x43a8, 0xd4b4)],
            [ChaCha20Block::buildUint32(0xcde9, 0xa11c),  1, ChaCha20Block::buildUint32(0x9bd3, 0x4239)],
            [ChaCha20Block::buildUint32(0xa7c4, 0x845c),  25, ChaCha20Block::buildUint32(0xb94f, 0x8908)],
            [ChaCha20Block::buildUint32(0xf716, 0x1477),  17, ChaCha20Block::buildUint32(0x28ef, 0xee2c)],
            [ChaCha20Block::buildUint32(0xf180, 0x0c82),  25, ChaCha20Block::buildUint32(0x05e3, 0x0019)],
            [ChaCha20Block::buildUint32(0x1e5f, 0x8fbb),  4, ChaCha20Block::buildUint32(0xe5f8, 0xfbb1)],
            [ChaCha20Block::buildUint32(0x4796, 0xab72),  17, ChaCha20Block::buildUint32(0x56e4, 0x8f2d)],
            [ChaCha20Block::buildUint32(0xe9eb, 0x0e88),  27, ChaCha20Block::buildUint32(0x474f, 0x5874)],
            [ChaCha20Block::buildUint32(0x305c, 0xc19a),  27, ChaCha20Block::buildUint32(0xd182, 0xe60c)],
            [ChaCha20Block::buildUint32(0x62b6, 0x37bf),  26, ChaCha20Block::buildUint32(0xfd8a, 0xd8de)],
            [ChaCha20Block::buildUint32(0xbc1b, 0xe6c7),  17, ChaCha20Block::buildUint32(0xcd8f, 0x7837)],
            [ChaCha20Block::buildUint32(0xfb3f, 0x9c58),  31, ChaCha20Block::buildUint32(0x7d9f, 0xce2c)],
            [ChaCha20Block::buildUint32(0xa8da, 0x9ebe),  7, ChaCha20Block::buildUint32(0x6d4f, 0x5f54)],
            [ChaCha20Block::buildUint32(0x2e79, 0x816a),  16, ChaCha20Block::buildUint32(0x816a, 0x2e79)],
            [ChaCha20Block::buildUint32(0x2cdd, 0x6e89),  8, ChaCha20Block::buildUint32(0xdd6e, 0x892c)],
            [ChaCha20Block::buildUint32(0x7d11, 0x33b2),  14, ChaCha20Block::buildUint32(0x4cec, 0x9f44)],
            [ChaCha20Block::buildUint32(0xf54c, 0x51e1),  30, ChaCha20Block::buildUint32(0x7d53, 0x1478)],
            [ChaCha20Block::buildUint32(0x89a1, 0xb65a),  28, ChaCha20Block::buildUint32(0xa89a, 0x1b65)],
            [ChaCha20Block::buildUint32(0x9d21, 0xbb9e),  0, ChaCha20Block::buildUint32(0x9d21, 0xbb9e)],
            [ChaCha20Block::buildUint32(0x57f6, 0x0f11),  15, ChaCha20Block::buildUint32(0x0788, 0xabfb)],
            [ChaCha20Block::buildUint32(0xadcf, 0x5ca5),  24, ChaCha20Block::buildUint32(0xa5ad, 0xcf5c)],
            [ChaCha20Block::buildUint32(0xde10, 0xf6a0),  13, ChaCha20Block::buildUint32(0x1ed4, 0x1bc2)],
            [ChaCha20Block::buildUint32(0x6529, 0xa959),  27, ChaCha20Block::buildUint32(0xcb29, 0x4d4a)],
            [ChaCha20Block::buildUint32(0xdd0c, 0x8cc2),  8, ChaCha20Block::buildUint32(0x0c8c, 0xc2dd)],
            [ChaCha20Block::buildUint32(0xdea3, 0x3b5c),  4, ChaCha20Block::buildUint32(0xea33, 0xb5cd)],
            [ChaCha20Block::buildUint32(0xf1b6, 0x8cd8),  8, ChaCha20Block::buildUint32(0xb68c, 0xd8f1)],
            [ChaCha20Block::buildUint32(0x4876, 0xa6ef),  13, ChaCha20Block::buildUint32(0xd4dd, 0xe90e)],
            [ChaCha20Block::buildUint32(0xb600, 0xac58),  17, ChaCha20Block::buildUint32(0x58b1, 0x6c01)],
            [ChaCha20Block::buildUint32(0x08fd, 0x6948),  13, ChaCha20Block::buildUint32(0xad29, 0x011f)],
            [ChaCha20Block::buildUint32(0x5fe9, 0x9270),  14, ChaCha20Block::buildUint32(0x649c, 0x17fa)],
            [ChaCha20Block::buildUint32(0x3bc9, 0x13af),  11, ChaCha20Block::buildUint32(0x489d, 0x79de)],
            [ChaCha20Block::buildUint32(0xa071, 0x36f6),  11, ChaCha20Block::buildUint32(0x89b7, 0xb503)],
            [ChaCha20Block::buildUint32(0x7253, 0x1589),  23, ChaCha20Block::buildUint32(0xc4b9, 0x298a)],
            [ChaCha20Block::buildUint32(0xa261, 0x955a),  9, ChaCha20Block::buildUint32(0xc32a, 0xb544)],
            [ChaCha20Block::buildUint32(0x3c49, 0x1a1a),  23, ChaCha20Block::buildUint32(0x0d1e, 0x248d)],
            [ChaCha20Block::buildUint32(0xc672, 0x2c82),  15, ChaCha20Block::buildUint32(0x1641, 0x6339)],
            [ChaCha20Block::buildUint32(0x95cb, 0x9630),  12, ChaCha20Block::buildUint32(0xb963, 0x095c)],
            [ChaCha20Block::buildUint32(0x28a0, 0xa877),  23, ChaCha20Block::buildUint32(0x3b94, 0x5054)],
            [ChaCha20Block::buildUint32(0xbc27, 0x6214),  24, ChaCha20Block::buildUint32(0x14bc, 0x2762)],
            [ChaCha20Block::buildUint32(0x990a, 0x686d),  3, ChaCha20Block::buildUint32(0xc853, 0x436c)],
            [ChaCha20Block::buildUint32(0x7df6, 0xc307),  23, ChaCha20Block::buildUint32(0x83be, 0xfb61)],
            [ChaCha20Block::buildUint32(0x7998, 0xbfda),  7, ChaCha20Block::buildUint32(0xcc5f, 0xed3c)],
            [ChaCha20Block::buildUint32(0xa59f, 0x595f),  7, ChaCha20Block::buildUint32(0xcfac, 0xafd2)],
        ];
    }

    /**
     * @dataProvider providerRotLeft
     */
    public function testRotLeft($value, $left, $expected) /* add ': void' in php 7.1 */
    {
        $this->assertEquals($expected, ChaCha20Block::rot_left($value, $left));
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
