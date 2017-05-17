<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Arguments;

class ArgumentsTest extends TestCase
{
    public function setUp()
    {
        $this->args = new Arguments();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /^Arguments not yet parsed$/
     */
    public function testNotParsed()
    {
        $this->args->getCommand();
    }

    public function testParseEmpty()
    {
        $this->args->parse(array());
        $this->assertSame(null, $this->args->getCommand());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /^No command provided$/
     */
    public function testParseGenSecretOptionFailed()
    {
        $this->args->parse(array());
        $this->args->getCommandOption("key_length");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /^Command option .* is not defined$/
     */
    public function testParseGenSecretOptionUnknown()
    {
        $this->args->parse(array("generate-secret"));
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->args->getCommandOption("this_option_does not exist");
    }

    public function testParseGenSecretDefault()
    {
        $this->args->parse(array("generate-secret"));
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->assertSame(1024, $this->args->getCommandOption("key_length"));
        $this->assertSame(16, $this->args->getCommandOption("salt_length"));
    }

    public function testParseGenSecretShort()
    {
        $this->args->parse(array("generate-secret", "-k", "123", "-s-456"));
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->assertSame(123, $this->args->getCommandOption("key_length"));
        $this->assertSame(-456, $this->args->getCommandOption("salt_length"));
    }

    public function testParseGenSecretLong()
    {
        $this->args->parse(array("generate-secret","--key-length=-78","--salt-length","90"));
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->assertSame(-78, $this->args->getCommandOption("key_length"));
        $this->assertSame(90, $this->args->getCommandOption("salt_length"));
    }
}
