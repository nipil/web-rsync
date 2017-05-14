<?php

declare(strict_types=1);

namespace WRS\Tests\KeyValue;

use PHPUnit\Framework\TestCase;

use WRS\KeyValue\CachedKeyValue,
    WRS\KeyValue\Interfaces\KeyValueInterface;

class CachedKeyValueTest extends TestCase
{
    const KEY = "test";
    const VALUE_INT = 42;
    const VALUE_STRING = "value";

    public function testHasCache() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // actual test without cache
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // setup cache
        $ckv->set_cache_string(self::KEY, self::VALUE_STRING);

        // actual test with cache
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Key not found in cache : .*$#
     */
    public function testGetCacheEmpty() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $ckv->get_cache(self::KEY);
    }

    public function testSetGetCacheString() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $ckv->set_cache_string(self::KEY, self::VALUE_STRING);

        // verify cache post-test
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_STRING, $ckv->get_cache(self::KEY));
    }

    public function testSetGetCacheInteger() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $ckv->set_cache_integer(self::KEY, self::VALUE_INT);

        // verify cache post-test
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_INT, $ckv->get_cache(self::KEY));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid type for key: .*$#
     */
    public function testInvalidCacheAccessFromStringToInteger() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));
        $ckv->set_cache_string(self::KEY, self::VALUE_STRING);
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->get_cache(self::KEY));

        // actual test
        $ckv->get_integer(self::KEY);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid type for key: .*$#
     */
    public function testInvalidCacheAccessFromIntegerToString() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));
        $ckv->set_cache_integer(self::KEY, self::VALUE_INT);
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->get_cache(self::KEY));

        // actual test
        $ckv->get_string(self::KEY);
    }

    public function testHasKeyWithoutCache() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->method('has_key')
            ->willReturn(TRUE);
        $kvi->expects($this->once())
            ->method('has_key')
            ->with($this->identicalTo(self::KEY));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $this->assertSame(TRUE, $ckv->has_key(self::KEY));
    }

    public function testHasKeyWithCache() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->never())
            ->method('has_key');

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));
        $ckv->set_cache_string(self::KEY, self::VALUE_STRING);
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));

        // actual test
        $this->assertSame(TRUE, $ckv->has_key(self::KEY));
    }

    public function testGetStringWithoutCache() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->method('get_string')
            ->willReturn(self::VALUE_STRING);
        $kvi->expects($this->once())
            ->method('get_string')
            ->with($this->identicalTo(self::KEY));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_STRING, $ckv->get_string(self::KEY));

        // verify cache post-test
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->get_cache(self::KEY));
    }

    public function testGetStringWithCache() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->never())
            ->method('get_string');

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));
        $ckv->set_cache_string(self::KEY, self::VALUE_STRING);
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->get_cache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_STRING, $ckv->get_string(self::KEY));
    }

    public function testSetString() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->once())
            ->method('set_string')
            ->with($this->identicalTo(self::KEY),
                   $this->identicalTo(self::VALUE_STRING));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $ckv->set_string(self::KEY, self::VALUE_STRING);

        // verify cache post-test
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_STRING, $ckv->get_cache(self::KEY));
    }

    public function testGetIntegerWithoutCache() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->method('get_integer')
            ->willReturn(self::VALUE_INT);
        $kvi->expects($this->once())
            ->method('get_integer')
            ->with($this->identicalTo(self::KEY));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_INT, $ckv->get_integer(self::KEY));

        // verify cache post-test
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->get_cache(self::KEY));
    }

    public function testGetIntegerWithCache() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->never())
            ->method('get_integer');

        // test
        $ckv = new CachedKeyValue($kvi);

        // setup cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));
        $ckv->set_cache_integer(self::KEY, self::VALUE_INT);
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->get_cache(self::KEY));

        // actual test
        $this->assertSame(self::VALUE_INT, $ckv->get_integer(self::KEY));
    }

    public function testSetInteger() {
        // mock
        $kvi = $this->createMock(KeyValueInterface::class);
        $kvi->expects($this->once())
            ->method('set_integer')
            ->with($this->identicalTo(self::KEY),
                   $this->identicalTo(self::VALUE_INT));

        // test
        $ckv = new CachedKeyValue($kvi);

        // verify cache pre-test
        $this->assertSame(FALSE, $ckv->has_cache(self::KEY));

        // actual test
        $ckv->set_integer(self::KEY, self::VALUE_INT);

        // verify cache post-test
        $this->assertSame(TRUE, $ckv->has_cache(self::KEY));
        $this->assertSame(self::VALUE_INT, $ckv->get_cache(self::KEY));
    }
}
