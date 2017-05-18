<?php

declare(strict_types=1);

namespace WRS\Tests\Storage;

use PHPUnit\Framework\TestCase;

use WRS\Storage\NullStorage;

class NullStorageTest extends TestCase
{
    public function testSave()
    {
        $ns = new NullStorage();
        $this->assertSame(true, $ns->save("key", "value"));
    }

    public function testExists()
    {
        $ns = new NullStorage();
        $this->assertSame(false, $ns->exists("anything"));
    }

    /**
     * @expectedException UnderflowException
     * @expectedExceptionMessageRegExp #^Cannot load key .*$#
     */
    public function testLoad()
    {
        $ns = new NullStorage();
        $result = $ns->load("key");
    }
}
