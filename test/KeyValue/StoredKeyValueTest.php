<?php

declare(strict_types=1);

namespace WRS\Tests\KeyValue;

use PHPUnit\Framework\TestCase;

use WRS\KeyValue\StoredKeyValue;
use WRS\Storage\Interfaces\StorageInterface;

class StoredKeyValueTest extends TestCase
{
    const KEY = "test";
    const ABSENT = "absent";
    const VALUE_STRING = "value";
    const VALUE_INT = 42;

    /**
     * Factor code to have StorageInterface::load throw exceptions
     */
    public function setupMockedStorageLoadException(string $key)
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('load')
            ->will(
                $this->throwException(
                    new \Exception(sprintf("Cannot load key %s", $key))
                )
            );
        return $storage;
    }

    public function testHasKey()
    {
        // mock StorageInterface::exists to return specific values
        $storage = $this->createMock(StorageInterface::class);
        $map = [[self::KEY, true], [self::ABSENT, false]];
        $storage->method('exists')
            ->will($this->returnValueMap($map));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $this->assertFalse($config->hasKey(self::ABSENT), "key should not exist");
        $this->assertTrue($config->hasKey(self::KEY), "key should exist");
    }

    public function testSetString()
    {
        // mock StorageInterface to verify that save is called once
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('save')
            ->with(
                $this->identicalTo(self::KEY),
                $this->identicalTo(self::VALUE_STRING)
            );

        // test with mock object
        $config = new StoredKeyValue($storage);
        $config->setString(self::KEY, self::VALUE_STRING);
    }

    public function testGetString()
    {
        // mock StorageInterface::exists to return specific values
        $storage = $this->createMock(StorageInterface::class);
        $map = [[self::KEY, self::VALUE_STRING], ];
        $storage->method('load')
            ->will($this->returnValueMap($map));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $text = $config->getString(self::KEY);
        $this->assertSame(self::VALUE_STRING, $text);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot load key .*$#
     */
    public function testGetStringMissing()
    {
        $storage = $this->setupMockedStorageLoadException(self::ABSENT);
        $config = new StoredKeyValue($storage);
        $config->getString(self::ABSENT);
    }

    public function testSetInteger()
    {
        // storage receives textual data
        $textual_value_int = sprintf("%d", self::VALUE_INT);

        // mock StorageInterface to verify that save is called once
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('save')
            ->with(
                $this->identicalTo(self::KEY),
                $this->identicalTo($textual_value_int)
            );

        // test with mock object
        $config = new StoredKeyValue($storage);
        $config->setInteger(self::KEY, self::VALUE_INT);
    }

    public function testGetInteger()
    {
        // storage receives textual data
        $textual_value_int = sprintf("%d", self::VALUE_INT);

        // mock StorageInterface::load to return textual representation of numbers
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('load')
            ->willReturn($textual_value_int);

        // test with mock object
        $config = new StoredKeyValue($storage);
        $int = $config->getInteger(self::KEY);
        $this->assertSame(self::VALUE_INT, $int);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot load key .*$#
     */
    public function testGetIntegerMissing()
    {
        $storage = $this->setupMockedStorageLoadException(self::ABSENT);
        $config = new StoredKeyValue($storage);
        $config->getInteger(self::ABSENT);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid integer .*$#
     */
    public function testGetIntegerInvalid()
    {
        // mock StorageInterface::load to return a non-integer
        $storage = $this->createMock(StorageInterface::class);
        $map = [[self::KEY, self::VALUE_STRING], ];
        $storage->method('load')
            ->will($this->returnValueMap($map));

        // test with mock object
        $config = new StoredKeyValue($storage);
        $config->getInteger(self::KEY);
    }
}
