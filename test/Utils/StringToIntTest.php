<?php

declare(strict_types=1);

namespace WRS\Tests\Utils;

use PHPUnit\Framework\TestCase;

use WRS\Utils\StringToInt;

class StringToIntTest extends TestCase
{
    public function providerStringToIntValid()
    {
        $data = array(
            "zero" => ["0", 0],
            "zero plus" => ["+0", 0],
            "zero minus" => ["-0", 0],
            "five minus" => ["-5", -5],
            "five plus" => ["+5", 5],
            "five" => ["5", 5],
        );
        if (PHP_INT_SIZE === 4) {
            $data = array_merge(
                $data,
                array(
                    "int max 32 bits" => [ "2147483647",  2147483647],
                    "int min 32 bits" => ["-2147483648", -2147483648],
                )
            );
        }
        if (PHP_INT_SIZE === 8) {
            $data = array_merge(
                $data,
                array(
                    "int max 64 bits" => [ "9223372036854775807",  9223372036854775807],
                    "int min 64 bits" => ["-9223372036854775808", -9223372036854775808],
                )
            );
        }
        return $data;
    }

    public function providerStringToIntInvalid()
    {
        $data = array(
            "empty" => ["", null],
            "text" => ["text", null],
            "mixed" => ["36mix", null],
            "reverse mixed" => ["mix36", null],
            "non-trimmed" => [" \t 6    \t", null],
            "plus plus" => ["++5", null],
            "minus minus" => ["--5", null],
            "plus minus" => ["+-5", null],
            "minus plus" => ["-+5", null],
        );
        return $data;
    }

    public function providerStringToIntTooLarge()
    {
        $data = array(
            "much too long" => ["-". str_repeat("9", 100), null],
        );
        if (PHP_INT_SIZE === 4) {
            $data = array_merge(
                $data,
                array(
                    "int max 32 bits +1" => [ "2147483648", null],
                    "int min 32 bits -1" => ["-2147483649", null],
                )
            );
        }
        if (PHP_INT_SIZE === 8) {
            $data = array_merge(
                $data,
                array(
                    "int max 64 bits +1" => [ "9223372036854775808", null],
                    "int min 64 bits -1" => ["-9223372036854775809", null],
                )
            );
        }
        return $data;
    }

    /**
     * @dataProvider providerStringToIntValid
     */
    public function testStringToIntSuccess(string $input, int $expected)
    {
        $value = StringToInt::convert($input);
        $this->assertSame($expected, $value);
    }

    /**
     * @dataProvider providerStringToIntInvalid
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid integer .*$#
     */
    public function testStringToIntInvalid(string $input, $null)
    {
        StringToInt::convert($input);
    }

    /**
     * @dataProvider providerStringToIntTooLarge
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Integer too large .+$#
     */
    public function testStringToIntTooLarge(string $input, $null)
    {
        StringToInt::convert($input);
    }
}
