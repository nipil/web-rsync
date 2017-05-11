<?php

declare(strict_types=1);

namespace WRS;

use PHPUnit\Framework\TestCase;

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

    public function testLoad()
    {
        $ns = new NullStorage();
        $result = $ns->load(self::KEY);
        $this->assertNull($result);
    }
}
