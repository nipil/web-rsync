<?php

declare(strict_types=1);

namespace WRS\Tests\Crypto;

use PHPUnit\Framework\TestCase;

use WRS\Crypto\VariableLengthAbstractSecret;

class VariableLengthAbstractSecretTest extends TestCase
{
    const VALID_NAME = "name";
    const VALID_KEY_LENGTH = 1;
    const VALID_SALT_LENGTH = 1;

    public function testConstructorValid()
    {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs([
                        self::VALID_NAME,
                        self::VALID_KEY_LENGTH,
                        self::VALID_SALT_LENGTH])
                     ->getMockForAbstractClass();
        $this->assertSame("name", $stub->get_name());
        $this->assertSame(self::VALID_KEY_LENGTH, $stub->get_key_length());
        $this->assertSame(self::VALID_SALT_LENGTH, $stub->get_salt_length());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid name$#
     */
    public function testConstructorInvalidname() {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs([
                        "",
                        self::VALID_KEY_LENGTH,
                        self::VALID_SALT_LENGTH])
                     ->getMockForAbstractClass();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid key length : -?\d$#
     */
    public function testConstructorInvalidKeyLength() {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs([
                        self::VALID_NAME,
                        0,
                        self::VALID_SALT_LENGTH])
                     ->getMockForAbstractClass();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid salt length : -?\d$#
     */
    public function testConstructorInvalidSaltLength() {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs([
                        self::VALID_NAME,
                        self::VALID_KEY_LENGTH,
                        0])
                     ->getMockForAbstractClass();
    }
}
