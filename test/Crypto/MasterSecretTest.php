<?php

declare(strict_types=1);

namespace WRS\Tests\Crypto;

use PHPUnit\Framework\TestCase;

use WRS\Crypto\MasterSecret,
    WRS\Crypto\Interfaces\RandomDataInterface,
    WRS\KeyValue\Interfaces\KeyValueInterface;

class MasterSecretTest extends TestCase
{
    public function testIds() {
        $randomizer = $this->createMock(RandomDataInterface::class);
        $keyvalue = $this->createMock(KeyValueInterface::class);

        $ms = new MasterSecret("master", 15, 10, $keyvalue, $randomizer);

        $this->assertSame("master-key", $ms->get_id_key(),"id key");
        $this->assertSame("master-salt", $ms->get_id_salt(),"id salt");
    }

    public function testGeneratePass() {
        $randomizer = $this->createMock(RandomDataInterface::class);
        $randomizer->method('get')
                   ->willReturn("TEXT");

        $keyvalue = $this->createMock(KeyValueInterface::class);

        $mastersecret = $this->getMockBuilder(MasterSecret::class)
                             ->setMethods(["set_key", "set_salt"])
                             ->setConstructorArgs(["name", 4, 4, $keyvalue, $randomizer])
                             ->getMock();

        $mastersecret->expects($this->once())
                     ->method("set_key")
                     ->with($this->identicalTo("TEXT"));

        $mastersecret->expects($this->once())
                     ->method("set_salt")
                     ->with($this->identicalTo("TEXT"));

        $mastersecret->generate();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid key length : -?\d+$#
     */
    public function testGenerateFailKey() {
        $randomizer = $this->createMock(RandomDataInterface::class);
        $randomizer->method('get')
                   ->willReturn("TEXT");

        $keyvalue = $this->createMock(KeyValueInterface::class);

        $mastersecret = new MasterSecret("name", 1, 4, $keyvalue, $randomizer);
        $mastersecret->generate();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid salt length : -?\d+$#
     */
    public function testGenerateFailSalt() {
        $randomizer = $this->createMock(RandomDataInterface::class);
        $randomizer->method('get')
                   ->willReturn("TEXT");

        $keyvalue = $this->createMock(KeyValueInterface::class);

        $mastersecret = new MasterSecret("name", 4, 1, $keyvalue, $randomizer);
        $mastersecret->generate();
    }

    /**** SecretKeeperInterface ****/

    public function testSetKeyPass() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->expects($this->once())
                 ->method('set_string')
                 ->with($this->identicalTo("name-key"), "KEY");

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $mastersecret->set_key("KEY");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid key length : -?\d+$#
     */
    public function testSetKeyFail() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->expects($this->never())
                 ->method('set_string');

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $mastersecret->set_key("TOOLONG");
    }

    public function testSetSaltPass() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->expects($this->once())
                 ->method('set_string')
                 ->with($this->identicalTo("name-salt"), "SALT");

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $mastersecret->set_salt("SALT");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid salt length : -?\d+$#
     */
    public function testSetSaltFail() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->expects($this->never())
                 ->method('set_string');

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $mastersecret->set_salt("TOOLONG");
    }

    public function testGetKeyPass() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->method('get_string')->willReturn("KEY");

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $this->assertSame("KEY", $mastersecret->get_key());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid key length : -?\d+$#
     */
    public function testGetKeyFail() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->method('get_string')->willReturn("TOOLONG");

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $mastersecret->get_key();
    }

    public function testGetSaltPass() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->method('get_string')->willReturn("SALT");

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $this->assertSame("SALT", $mastersecret->get_salt());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid salt length : -?\d+$#
     */
    public function testGetSaltFail() {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->method('get_string')->willReturn("TOOLONG");

        $mastersecret = new MasterSecret("name", 3, 4, $keyvalue, $randomizer);
        $mastersecret->get_salt();
    }
}
