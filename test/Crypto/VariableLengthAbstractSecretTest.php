<?php

declare(strict_types=1);

namespace WRS\Tests\Crypto;

use PHPUnit\Framework\TestCase;

use WRS\Crypto\Abstracts\VariableLengthAbstractSecret;

class VariableLengthAbstractSecretTest extends TestCase
{
    const VALID_NAME = "name";
    const VALID_KEY_LENGTH = 15;
    const VALID_SALT_LENGTH = 10;

    public function testConstructorValid()
    {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs([self::VALID_NAME,
                                           self::VALID_KEY_LENGTH,
                                           self::VALID_SALT_LENGTH])
                     ->getMockForAbstractClass();
        $this->assertSame("name", $stub->getName());
        $this->assertSame(self::VALID_KEY_LENGTH, $stub->getKeyLength());
        $this->assertSame(self::VALID_SALT_LENGTH, $stub->getSaltLength());

        return $stub;
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid name$#
     */
    public function testConstructorInvalidname()
    {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs(["",
                                           self::VALID_KEY_LENGTH,
                                           self::VALID_SALT_LENGTH])
                     ->getMockForAbstractClass();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid key length : -?\d+$#
     */
    public function testConstructorInvalidKeyLength()
    {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs([self::VALID_NAME,
                                           0,
                                           self::VALID_SALT_LENGTH])
                     ->getMockForAbstractClass();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid salt length : -?\d+$#
     */
    public function testConstructorInvalidSaltLength()
    {
        $stub = $this->getMockBuilder(VariableLengthAbstractSecret::class)
                     ->setConstructorArgs([self::VALID_NAME,
                                           self::VALID_KEY_LENGTH,
                                           0])
                     ->getMockForAbstractClass();
    }

    /**
     * @depends testConstructorValid
     */
    public function testValidateKeyPass($stub)
    {
        $str = str_repeat("_", self::VALID_KEY_LENGTH);
        $this->assertSame(true, $stub->validateKey($str));
    }

    /**
     * @depends testConstructorValid
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid key length : -?\d+$#
     */
    public function testValidateKeyFail($stub)
    {
        $str = str_repeat("_", self::VALID_KEY_LENGTH + 1);
        $stub->validateKey($str);
    }

    /**
     * @depends testConstructorValid
     */
    public function testValidateSaltPass($stub)
    {
        $str = str_repeat("_", self::VALID_SALT_LENGTH);
        $this->assertSame(true, $stub->validateSalt($str));
    }

    /**
     * @depends testConstructorValid
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid salt length : -?\d+$#
     */
    public function testValidateSaltFail($stub)
    {
        $str = str_repeat("_", self::VALID_SALT_LENGTH + 1);
        $stub->validateSalt($str);
    }
}
