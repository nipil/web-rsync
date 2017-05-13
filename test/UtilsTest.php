<?php

declare(strict_types=1);

namespace WRS\Tests;

use PHPUnit\Framework\TestCase;

use WRS\Utils;

class UtilsTest extends TestCase
{
    public static function providerStringToIntValid() {
        $data = array(
            "zero" => ["0", 0],
            "zero plus" => ["+0", 0],
            "zero minus" => ["-0", 0],
            "five minus" => ["-5", -5],
            "five plus" => ["+5", 5],
            "five" => ["5", 5],
        );
        if (PHP_INT_SIZE === 4) {
            $data = array_merge($data, array(
                "int max 32 bits" => [ "2147483647",  2147483647],
                "int min 32 bits" => ["-2147483648", -2147483648],
            ));
        }
        if (PHP_INT_SIZE === 8) {
            $data = array_merge($data, array(
                "int max 64 bits" => [ "9223372036854775807",  9223372036854775807],
                "int min 64 bits" => ["-9223372036854775808", -9223372036854775808],
            ));
        }
        return $data;
    }

    public static function providerStringToIntInvalid() {
        $data = array(
            "empty" => ["", NULL],
            "text" => ["text", NULL],
            "mixed" => ["36mix", NULL],
            "reverse mixed" => ["mix36", NULL],
            "non-trimmed" => [" \t 6    \t", NULL],
            "plus plus" => ["++5", NULL],
            "minus minus" => ["--5", NULL],
            "plus minus" => ["+-5", NULL],
            "minus plus" => ["-+5", NULL],
        );
        return $data;
    }

    public static function providerStringToIntTooLarge() {
        $data = array(
            "much too long" => ["-". str_repeat("9", 100), NULL],
        );
        if (PHP_INT_SIZE === 4) {
            $data = array_merge($data, array(
                "int max 32 bits +1" => [ "2147483648", NULL],
                "int min 32 bits -1" => ["-2147483649", NULL],
            ));
        }
        if (PHP_INT_SIZE === 8) {
            $data = array_merge($data, array(
                "int max 64 bits +1" => [ "9223372036854775808", NULL],
                "int min 64 bits -1" => ["-9223372036854775809", NULL],
            ));
        }
        return $data;
    }

    /**
     * @dataProvider providerStringToIntValid
     */
    public function testStringToIntSuccess(string $input, int $expected) {
        $value = Utils::StringToInt($input);
        $this->assertSame($expected, $value);
    }

    /**
     * @dataProvider providerStringToIntInvalid
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid integer .*$#
     */
    public function testStringToIntInvalid(string $input, $null) {
        Utils::StringToInt($input);
    }

    /**
     * @dataProvider providerStringToIntTooLarge
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Integer too large .+$#
     */
    public function testStringToIntTooLarge(string $input, $null) {
        Utils::StringToInt($input);
    }
}
