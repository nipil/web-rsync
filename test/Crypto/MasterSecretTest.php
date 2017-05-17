<?php

declare(strict_types=1);

namespace WRS\Tests\Crypto;

use PHPUnit\Framework\TestCase;

use WRS\Crypto\MasterSecret;
use WRS\Crypto\Interfaces\RandomDataInterface;
use WRS\KeyValue\Interfaces\KeyValueInterface;

class MasterSecretTest extends TestCase
{
    private $keyvalue;
    private $randomizer;

    public function providerInvalidLength()
    {
        return array(
            [-1, null],
            [0, null],
        );
    }

    public function setUp()
    {
        $this->keyvalue = $this->createMock(KeyValueInterface::class);
        $this->randomizer = $this->createMock(RandomDataInterface::class);
    }

    public function testIds()
    {
        $ms = new MasterSecret("master", $this->keyvalue, $this->randomizer);
        $this->assertSame("master-key", $ms->getIdKey(), "id key");
        $this->assertSame("master-salt", $ms->getIdSalt(), "id salt");
    }

    public function testGeneratePass()
    {
        $this->randomizer->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [3],
                [4]
            )
            ->will($this->onConsecutiveCalls(
                "KEY",
                "SALT"
            ));

        $this->keyvalue->expects($this->exactly(2))
            ->method('setString')
            ->withConsecutive(
                ["master-key", "KEY"],
                ["master-salt", "SALT"]
            );

        $mastersecret = new MasterSecret("master", $this->keyvalue, $this->randomizer);
        $mastersecret->generate(3, 4);
    }

    /**
     * @dataProvider providerInvalidLength
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid key length : -?\d+$#
     */
    public function testGenerateFailKey(int $input, $null)
    {
        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $mastersecret->generate($input, 1);
    }

    /**
     * @dataProvider providerInvalidLength
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid salt length : -?\d+$#
     */
    public function testGenerateFailSalt(int $input, $null)
    {
        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $mastersecret->generate(1, $input);
    }

    /* MasterKeyInterface */

    public function testSetGetKey()
    {
        $randomizer = $this->createMock(RandomDataInterface::class);

        $keyvalue = $this->createMock(KeyValueInterface::class);
        $keyvalue->expects($this->once())
            ->method('setString')
            ->with(
                $this->identicalTo("name-key"),
                $this->identicalTo("KEY")
            );

        $mastersecret = new MasterSecret("name", $keyvalue, $randomizer);
        $mastersecret->setKey("KEY");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Key cannot be empty$#
     */
    public function testSetKeyFail()
    {
        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $mastersecret->setKey("");
    }

    public function testSetGetSalt()
    {
        $this->keyvalue = $this->createMock(KeyValueInterface::class);
        $this->keyvalue->expects($this->once())
            ->method('setString')
            ->with(
                $this->identicalTo("name-salt"),
                $this->identicalTo("SALT")
            );

        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $mastersecret->setSalt("SALT");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Salt cannot be empty$#
     */
    public function testSetSaltFail()
    {
        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $mastersecret->setSalt("");
    }

    public function testGetKeyPass()
    {
        $this->keyvalue->expects($this->once())
            ->method('getString')
            ->with($this->identicalTo("name-key"))
            ->willReturn("KEY");

        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $this->assertSame("KEY", $mastersecret->getKey());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Key cannot be empty$#
     */
    public function testGetKeyFailEmpty()
    {
        $this->keyvalue->expects($this->once())
            ->method('getString')
            ->with($this->identicalTo("name-key"))
            ->willReturn("");

        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $mastersecret->getKey();
    }

    public function testGetSaltPass()
    {
        $this->keyvalue->expects($this->once())
            ->method('getString')
            ->with($this->identicalTo("name-salt"))
            ->willReturn("SALT");

        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $this->assertSame("SALT", $mastersecret->getSalt());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Salt cannot be empty$#
     */
    public function testGetSaltFailEmpty()
    {
        $this->keyvalue->expects($this->once())
            ->method('getString')
            ->with($this->identicalTo("name-salt"))
            ->willReturn("");

        $mastersecret = new MasterSecret("name", $this->keyvalue, $this->randomizer);
        $mastersecret->getSalt();
    }
}
