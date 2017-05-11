<?php

declare(strict_types=1);

namespace WRS;

use PHPUnit\Framework\TestCase;

class StoredKeyValueTest extends TestCase
{
    const KEY = "test";
    const VALUE_STRING = "value";
    const VALUE_INT = 42;

    public function providerStringToIntValid() {
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
        $value = StoredKeyValue::StringToInt($input);
        $this->assertSame($expected, $value);
    }

    public function providerStringToIntInvalid() {
        $data = array(
            "zero" => ["+0", 1],
            "text" => ["text", 1],
            "mixed" => ["36mix", 1],
            "non-trimmed" => [" \t 6    \t", 1],
        );
        return $data;
    }

    /**
     * @dataProvider             providerStringToIntInvalid
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid integer
     */
    public function testStringToIntFail(string $input, int $expected) {
        $value = StoredKeyValue::StringToInt($input);
        $this->assertSame($expected, $value);
    }

    public function testHasKey() {
        $config = new StoredKeyValue(new NullStorage());
        $this->assertFalse($config->has_key(self::KEY), "key should not exist");
        $config->set_integer(self::KEY, self::VALUE_INT);
        $this->assertTrue($config->has_key(self::KEY), "key should exist");
    }

    public function testSetString() {
        $config = new StoredKeyValue(new NullStorage());
        $config->set_string(self::KEY, self::VALUE_STRING);
        $value = $config->get_string(self::KEY);
        $this->assertSame(self::VALUE_STRING, $value);
    }

    /**
     * @dataProvider             providerStringToIntInvalid
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot load key test
     */
    public function testGetStringFail() {
        $config = new StoredKeyValue(new NullStorage());
        $config->get_string(self::KEY);
    }

    public function testSetInteger() {
        $config = new StoredKeyValue(new NullStorage());
        $config->set_integer(self::KEY, self::VALUE_INT);
        $value = $config->get_integer(self::KEY);
        $this->assertSame(self::VALUE_INT, $value);
    }

    /**
     * @dataProvider             providerStringToIntInvalid
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot load key test
     */
    public function testGetIntegerFail() {
        $config = new StoredKeyValue(new NullStorage());
        $config->get_integer(self::KEY);
    }
}
