<?php

declare(strict_types=1);

namespace WRS\Tests\KeyValue;

use PHPUnit\Framework\TestCase;

use WRS\KeyValue\CachedKeyValue;
use WRS\KeyValue\Interfaces\KeyValueInterface;

class CachedKeyValueTest extends TestCase
{
    const KEY = "test";
    const VALUE_INT = 42;
    const VALUE_STRING = "value";

    public function testHasCache()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // actual test without cache
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // setup cache
        $ckv->setCacheString(self::KEY, self::VALUE_STRING);

        // actual test with cache
        $this->assertSame(true, $ckv->hasCache(self::KEY));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp #^Key not found in cache : .*$#
     */
    public function testGetCacheEmpty()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $ckv->getCache(self::KEY);
    }

    public function testSetGetCacheString()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $ckv->setCacheString(self::KEY, self::VALUE_STRING);

        // verify cache post-test
        $this->assertSame(true, $ckv->hasCache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_STRING, $ckv->getCache(self::KEY));
    }

    public function testSetGetCacheInteger()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $ckv->setCacheInteger(self::KEY, self::VALUE_INT);

        // verify cache post-test
        $this->assertSame(true, $ckv->hasCache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_INT, $ckv->getCache(self::KEY));
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionMessageRegExp #^Invalid type for key: .*$#
     */
    public function testInvalidCacheAccessFromStringToInteger()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));
        $ckv->setCacheString(self::KEY, self::VALUE_STRING);
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->getCache(self::KEY));

        // actual test
        $ckv->getInteger(self::KEY);
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionMessageRegExp #^Invalid type for key: .*$#
     */
    public function testInvalidCacheAccessFromIntegerToString()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));
        $ckv->setCacheInteger(self::KEY, self::VALUE_INT);
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->getCache(self::KEY));

        // actual test
        $ckv->getString(self::KEY);
    }

    public function testHasKeyWithoutCache()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->method('hasKey')
            ->willReturn(true);
        $kvi->expects($this->once())
            ->method('hasKey')
            ->with($this->identicalTo(self::KEY));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $this->assertSame(true, $ckv->hasKey(self::KEY));
    }

    public function testHasKeyWithCache()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->never())
            ->method('hasKey');

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache
        $this->assertSame(false, $ckv->hasCache(self::KEY));
        $ckv->setCacheString(self::KEY, self::VALUE_STRING);
        $this->assertSame(true, $ckv->hasCache(self::KEY));

        // actual test
        $this->assertSame(true, $ckv->hasKey(self::KEY));
    }

    public function testGetStringWithoutCache()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->method('getString')
            ->willReturn(self::VALUE_STRING);
        $kvi->expects($this->once())
            ->method('getString')
            ->with($this->identicalTo(self::KEY));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_STRING, $ckv->getString(self::KEY));

        // verify cache post-test
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->getCache(self::KEY));
    }

    public function testGetStringWithCache()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->never())
            ->method('getString');

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));
        $ckv->setCacheString(self::KEY, self::VALUE_STRING);
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->getCache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_STRING, $ckv->getString(self::KEY));
    }

    public function testSetString()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->once())
            ->method('setString')
            ->with(
                $this->identicalTo(self::KEY),
                $this->identicalTo(self::VALUE_STRING)
            );

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $ckv->setString(self::KEY, self::VALUE_STRING);

        // verify cache post-test
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->getCache(self::KEY));
    }

    public function testGetIntegerWithoutCache()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->method('getInteger')
            ->willReturn(self::VALUE_INT);
        $kvi->expects($this->once())
            ->method('getInteger')
            ->with($this->identicalTo(self::KEY));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_INT, $ckv->getInteger(self::KEY));

        // verify cache post-test
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->getCache(self::KEY));
    }

    public function testGetIntegerWithCache()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->never())
            ->method('getInteger');

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));
        $ckv->setCacheInteger(self::KEY, self::VALUE_INT);
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->getCache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_INT, $ckv->getInteger(self::KEY));
    }

    public function testSetInteger()
    {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->once())
            ->method('setInteger')
            ->with(
                $this->identicalTo(self::KEY),
                $this->identicalTo(self::VALUE_INT)
            );

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(false, $ckv->hasCache(self::KEY));

        // actual test
        $ckv->setInteger(self::KEY, self::VALUE_INT);

        // verify cache post-test
        $this->assertSame(true, $ckv->hasCache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->getCache(self::KEY));
    }
}
