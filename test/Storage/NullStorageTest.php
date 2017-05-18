<?php

declare(strict_types=1);

namespace WRS\Tests\Storage;

use PHPUnit\Framework\TestCase;

use WRS\Storage\NullStorage;

class NullStorageTest extends TestCase
{
    const KEY = "test";
    const VALUE = "value";

    public function testSave()
    {
        $ns = new NullStorage();
        $ns->save(self::KEY, self::VALUE);
        $this->assertTrue(true);
    }

    public function testExists()
    {
        $ns = new NullStorage();
        $ns->save(self::KEY, self::VALUE);
        $this->assertSame(false, $ns->exists(self::KEY));
    }

    /**
     * @expectedException UnderflowException
     * @expectedExceptionMessageRegExp #^Cannot load key .*$#
     */
    public function testLoad()
    {
        $ns = new NullStorage();
        $result = $ns->load(self::KEY);
    }
}
