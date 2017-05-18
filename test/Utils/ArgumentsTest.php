<?php

declare(strict_types=1);

namespace WRS\Tests\Utils;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Utils\Arguments;

class ArgumentsTest extends TestCase
{
    public function setUp()
    {
        $this->args = new Arguments();
    }

    public function testParseConstructorOverride()
    {
        $args = new Arguments(
            array(
                "program_name",
                "generate-secret",
                "-k",
                "123"
            )
        );
        $args->parse();
        $this->assertSame("generate-secret", $args->getCommand());
        $this->assertSame(123, $args->getCommandOption("key_length"));
        $this->assertSame(16, $args->getCommandOption("salt_length"));
    }

    public function testParseConstructorLocalOverride()
    {
        $args = new Arguments(
            array(
                "program_name",
                "generate-secret",
                "-k",
                "123"
            )
        );
        $args->parse(
            array(
                "program_name",
                "generate-secret",
                "-s-456"
            )
        );
        $this->assertSame("generate-secret", $args->getCommand());
        $this->assertSame(1024, $args->getCommandOption("key_length"));
        $this->assertSame(-456, $args->getCommandOption("salt_length"));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessageRegExp /^Arguments not yet parsed$/
     */
    public function testNotParsed()
    {
        $this->args->getCommand();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessageRegExp /^Argument list must start with program name$/
     */
    public function testParseLocalEmpty()
    {
        $this->args->parse(array());
        $this->assertSame(null, $this->args->getCommand());
    }

    public function testParseLocalNoCommand()
    {
        $this->args->parse(array("program_name"));
        $this->assertSame(null, $this->args->getCommand());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /^No command provided$/
     */
    public function testParseLocalGenSecretOptionFailed()
    {
        $this->args->parse(array("program_name"));
        $this->args->getCommandOption("key_length");
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessageRegExp /^Command option .* is not defined$/
     */
    public function testParseLocalGenSecretOptionUnknown()
    {
        $this->args->parse(
            array(
                "program_name",
                "generate-secret"
            )
        );
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->args->getCommandOption("this_option_does not exist");
    }

    public function testParseLocalGenSecretDefault()
    {
        $this->args->parse(
            array(
                "program_name",
                "generate-secret",
            )
        );
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->assertSame(1024, $this->args->getCommandOption("key_length"));
        $this->assertSame(16, $this->args->getCommandOption("salt_length"));
    }

    public function testParseLocalGenSecretShort()
    {
        $this->args->parse(
            array(
                "program_name",
                "generate-secret",
                "-k",
                "123",
                "-s-456"
            )
        );
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->assertSame(123, $this->args->getCommandOption("key_length"));
        $this->assertSame(-456, $this->args->getCommandOption("salt_length"));
    }

    public function testParseLocalGenSecretLong()
    {
        $this->args->parse(
            array(
                "program_name",
                "generate-secret",
                "--key-length=-78",
                "--salt-length","90"
            )
        );
        $this->assertSame("generate-secret", $this->args->getCommand());
        $this->assertSame(-78, $this->args->getCommandOption("key_length"));
        $this->assertSame(90, $this->args->getCommandOption("salt_length"));
    }
}
