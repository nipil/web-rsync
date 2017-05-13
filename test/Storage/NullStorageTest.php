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
        $this->assertTrue(TRUE);
    }

    public function testExists()
    {
        $ns = new NullStorage();
        $ns->save(self::KEY, self::VALUE);
        $this->assertSame(FALSE, $ns->exists(self::KEY));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot load key test
     */
    public function testLoad()
    {
        $ns = new NullStorage();
        $result = $ns->load(self::KEY);
    }
}
