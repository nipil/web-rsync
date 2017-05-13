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
            "zero minus" => ["-0", 0],
            "five minus" => ["-5", -5],
            "five" => ["5", 5],
            "int max 32 bits" => [ "2147483647",  2147483647],
            "int min 32 bits" => ["-2147483648", -2147483648],
        );
        if (PHP_INT_SIZE === 8) {
            $data = array_merge($data, array(
                "int max 64 bits" => [ "9223372036854775807",  9223372036854775807],
                "int min 64 bits" => ["-9223372036854775808", -9223372036854775808],
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

    public static function providerStringToIntInvalid() {
        $data = array(
            "empty" => ["", NULL],
            "zero" => ["+0", NULL],
            "text" => ["text", NULL],
            "mixed" => ["36mix", NULL],
            "non-trimmed" => [" \t 6    \t", NULL],
        );
        return $data;
    }

    /**
     * @dataProvider providerStringToIntInvalid
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid integer .*$#
     */
    public function testStringToIntFail(string $input, $null) {
        $value = Utils::StringToInt($input);
    }
}
