<?php

declare(strict_types=1);

namespace WRS\Tests;

use PHPUnit\Framework\TestCase;

use WRS\Storage\StorageInterface,
    WRS\KeyValue\StoredKeyValue;

class StoredKeyValueTest extends TestCase
{
    const KEY = "test";
    const ABSENT = "absent";
    const VALUE_STRING = "value";
    const VALUE_INT = 42;

    /**
     * mock StorageInterface::load to throw exceptions
     */
    public function setupMockedStorageLoadException(string $key) {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('load')
                ->will($this->throwException(new \Exception(sprintf("Cannot load key %s",$key))));
        return $storage;
    }

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
        $value = StoredKeyValue::StringToInt($input);
    }

    public function testHasKey() {
        // mock StorageInterface::exists to return specific values
        $storage = $this->createMock(StorageInterface::class);
        $map = [[self::KEY, TRUE], [self::ABSENT, FALSE]];
        $storage->method('exists')
                ->will($this->returnValueMap($map));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $this->assertFalse($config->has_key(self::ABSENT), "key should not exist");
        $this->assertTrue($config->has_key(self::KEY), "key should exist");
    }

    public function testSetString() {
        // mock StorageInterface to verify that save is called once
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
                ->method('save')
                ->with($this->identicalTo(self::KEY),
                       $this->identicalTo(self::VALUE_STRING));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $config->set_string(self::KEY, self::VALUE_STRING);
    }

    public function testGetString() {
        // mock StorageInterface::exists to return specific values
        $storage = $this->createMock(StorageInterface::class);
        $map = [[self::KEY, self::VALUE_STRING], ];
        $storage->method('load')
                ->will($this->returnValueMap($map));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $text = $config->get_string(self::KEY);
        $this->assertSame(self::VALUE_STRING, $text);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot load key .*$#
     */
    public function testGetStringMissing() {
        $storage = $this->setupMockedStorageLoadException(self::ABSENT);
        $config = new StoredKeyValue($storage);
        $config->get_string(self::ABSENT);
    }

    public function testSetInteger() {
        // storage receives textual data
        $textual_value_int = sprintf("%d", self::VALUE_INT);

        // mock StorageInterface to verify that save is called once
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
                ->method('save')
                ->with($this->identicalTo(self::KEY),
                       $this->identicalTo($textual_value_int));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $config->set_integer(self::KEY, self::VALUE_INT);
    }

    /**
     * @dataProvider providerStringToIntValid
     *
     * $input is the textual representation of $expected
     */
    public function testGetInteger(string $input, int $expected) {
        // mock StorageInterface::load to return textual representation of numbers
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('load')
                ->willReturn($input);

        // test with mock object
        $config = new StoredKeyValue($storage);
        $int = $config->get_integer(self::KEY);
        $this->assertSame($expected, $int);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot load key .*$#
     */
    public function testGetIntegerMissing() {
        $storage = $this->setupMockedStorageLoadException(self::ABSENT);
        $config = new StoredKeyValue($storage);
        $config->get_integer(self::ABSENT);
    }

    /**
     * @dataProvider providerStringToIntInvalid
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid integer .*$#
     */
    public function testGetIntegerInvalid(string $input, $null) {
        // mock StorageInterface::load to return input
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('load')
                ->will($this->returnArgument(0));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $config->get_integer($input);
    }
}
