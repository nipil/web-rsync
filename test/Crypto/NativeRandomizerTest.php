<?php

declare(strict_types=1);

namespace WRS\Tests\Crypto;

use PHPUnit\Framework\TestCase;

use WRS\Crypto\NativeRandomizer;

class NativeRandomizerTest extends TestCase
{
    public function testGetValidLength() {
        $nr = new NativeRandomizer();
        $this->assertSame(0, strlen($nr->get(0)));
        $this->assertSame(10, strlen($nr->get(10)));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /^Invalid number of bytes requested : -?\d+/
     */
    public function testGetInvalidLength() {
        $nr = new NativeRandomizer();
        $nr->get(-1);
    }
}
