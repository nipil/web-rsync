<?php

declare(strict_types=1);

namespace WRS\Tests\KeyValue;

use PHPUnit\Framework\TestCase;

use WRS\KeyValue\StoredKeyValue;
use WRS\Storage\Interfaces\StorageInterface;

class StoredKeyValueTest extends TestCase
{
    private $storage;

    public function setUp()
    {
        $this->storage = $this->createMock(StorageInterface::class);
    }

    public function testHasKey()
    {
        $map = [
            ["present", true],
            ["absent", false]
        ];

        $this->storage
            ->expects($this->exactly(2))
            ->method("exists")
            ->will($this->returnValueMap($map));

        $config = new StoredKeyValue($this->storage);

        $this->assertSame(false, $config->hasKey("absent"), "key should not exist");
        $this->assertSame(true, $config->hasKey("present"), "key should exist");
    }

    public function testSetString()
    {
        $this->storage
            ->expects($this->once())
            ->method("save")
            ->with(
                $this->identicalTo("key"),
                $this->identicalTo("string")
            );

        $config = new StoredKeyValue($this->storage);

        $config->setString("key", "string");
    }

    public function testGetString()
    {
        $map = [
            ["key", "string"],
        ];

        $this->storage
            ->expects($this->once())
            ->method("load")
            ->will($this->returnValueMap($map));

        $config = new StoredKeyValue($this->storage);

        $this->assertSame("string", $config->getString("key"));
    }

    public function testSetInteger()
    {
        $this->storage
            ->expects($this->once())
            ->method("save")
            ->with(
                $this->identicalTo("key"),
                $this->identicalTo("1664")
            );

        $config = new StoredKeyValue($this->storage);

        $config->setInteger("key", 1664);
    }

    public function testGetInteger()
    {
        $map = [
            ["key", "1664"],
        ];

        $this->storage
            ->expects($this->once())
            ->method("load")
            ->will($this->returnValueMap($map));

        $config = new StoredKeyValue($this->storage);

        $this->assertSame(1664, $config->getInteger("key"));
    }
}
