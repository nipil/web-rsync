<?php

declare(strict_types=1);

namespace WRS\Tests;

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
