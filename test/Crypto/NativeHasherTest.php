<?php

declare(strict_types=1);

namespace WRS\Tests\Crypto;

use PHPUnit\Framework\TestCase;

use WRS\Crypto\NativeHasher;

class NativeHasherTest extends TestCase
{
    const FUNC = "md5";
    const MSG = "The quick brown fox jumps over the lazy dog";
    const HASH = "9e107d9d372bb6826bd81d3542a419d6";
    const KEY = "key";
    const HMAC = "80070713463e7749b90c2dc24911e275";

    public function providerAlgo()
    {
        return array_map(
            function ($algo) {
                return [$algo, null];
            },
            hash_algos()
        );
    }

    /**
     * @dataProvider providerAlgo
     */
    public function testConstructorValid(string $algo, $null)
    {
        $nh = new NativeHasher($algo);
        $this->assertSame($algo, $nh->getHashFunction());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid hash function : .*$#
     */
    public function testConstructorInvalid()
    {
        $nh = new NativeHasher("this_is_an_invalid_hash_function");
    }

    public function testHash()
    {
        $nh = new NativeHasher(self::FUNC);
        $result = $nh->hash(self::MSG);
        $this->assertSame(hex2bin(self::HASH), $result);
    }

    public function testHmac()
    {
        $nh = new NativeHasher(self::FUNC);
        $result = $nh->hmac(self::MSG, self::KEY);
        $this->assertSame(hex2bin(self::HMAC), $result);
    }
}
