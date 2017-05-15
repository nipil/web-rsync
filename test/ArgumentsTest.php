<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Arguments;

class ArgumentsTest extends TestCase
{
    public function testConstructor()
    {
        $args = new Arguments();
        $this->assertSame(array(), $args->getArguments());
    }

    public function testSetGetArguments()
    {
        $args = new Arguments();
        $val = array("toto" => "titi", "tata" => 42);
        $args->setArguments($val);

        $this->assertSame($val, $args->getArguments($val));
    }

    public function testGetParam()
    {
        $args = new Arguments();
        $val = array("toto" => "titi", "tata" => 42);
        $args->setArguments($val);

        $this->assertSame("titi", $args->getParam("toto"));
        $this->assertSame(42, $args->getParam("tata"));
        $this->assertSame(null, $args->getParam("tutu"));
    }

    public function testGetAction()
    {
        $args = new Arguments();

        $this->assertSame(null, $args->getAction());

        $val = array("action" => "dosomething!");
        $args->setArguments($val);

        $this->assertSame("dosomething!", $args->getAction());
    }

    public function testParse()
    {
        $args = new Arguments();
        $args->parse();
        $this->assertInternalType("array", $args->getArguments());
    }
}
