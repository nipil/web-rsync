<?php

declare(strict_types=1);

namespace WRS\Tests;

use PHPUnit\Framework\TestCase;

use WRS\Utils;

class UtilsHexToStringTest extends TestCase
{
    public function providerInvalidHex() {
        return array(
            ["toto", NULL],
            ["1", NULL],
            ["123", NULL],
            ["g", NULL],
            ["a", NULL],
            ["A", NULL],
        );
    }

    public function providerValidNoLength() {
        return array(
            ["", ""],
            ["12", chr(0x12)],
            ["1f", chr(0x1f)],
            ["12FA", chr(0x12).chr(0xfa)],
            ["123456", chr(0x12).chr(0x34).chr(0x56)],
            ["f46b604f", chr(0xf4).chr(0x6b).chr(0x60).chr(0x4f)],
        );
    }

    /*
     * splice provider set to insert manipulated length
     */
    public function addLength(array $input, \Closure $length_func) {
        return array_map(function($item) use ($length_func) {
            // var_dump($item);
            $len = strlen($item[0]);
            if ($len % 2 === 1) {
                throw new \Exception(sprintf("Invalid length %d for provided hex %s", $len, $item[0]));
            }
            array_splice($item, 1, 0, $length_func($len));
            return $item;
        }, $input);
    }

    public function providerValidWithLengthPass() {
        return $this->addLength(
            $this->providerValidNoLength(),
            function($original_length) {
                // req len = bin len = hex len / 2
                // so that it is valid
                return $original_length >> 1;
            });
    }

    public function providerValidWithLengthFail() {
        return $this->addLength(
            $this->providerValidNoLength(),
            function($original_length) {
                // req len = -1
                // so that it always fails
                return -1;
            });
    }

    /**
     * @dataProvider providerInvalidHex
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /^Invalid hex string .+$/
     */
    public function testHexToStringInvalid(string $input, $null) {
        Utils::HexToString($input);
    }

    /**
     * @dataProvider providerValidNoLength
     */
    public function testHexToStringValidNoLength(string $input, string $expected) {
        $this->assertSame(bin2hex($expected), bin2hex(Utils::HexToString($input)));
    }

    /**
     * @dataProvider providerValidWithLengthFail
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid length -?\d+ for .*$#
     */
    public function testHexToStringValidWithLengthFail(string $input, int $length, $unused) {
        Utils::HexToString($input, $length);
    }

    /**
     * @dataProvider providerValidWithLengthPass
     */
    public function testHexToStringValidWithLengthPass(string $input, int $length, string $expected) {
        $this->assertSame($expected, Utils::HexToString($input, $length));
    }
}
