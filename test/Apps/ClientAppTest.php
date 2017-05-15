<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Arguments;
use WRS\Apps\ClientApp;
use WRS\Crypto\MasterSecret;

class ClientAppTest extends TestCase
{
    private $logger;
    private $master_secret;
    private $arguments;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->master_secret = $this->createMock(MasterSecret::class);
        $this->arguments = $this->createMock(Arguments::class);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^No action provided$#
     */
    public function testRunNoAction()
    {
        $app = new ClientApp(
            $this->arguments,
            $this->master_secret,
            $this->logger
        );
        $app->run();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Unknown action : .*$#
     */
    public function testRunUnknownAction()
    {
        $this->arguments->method("getAction")
                        ->willReturn("this_is_an_unknown_action");

        $app = new ClientApp(
            $this->arguments,
            $this->master_secret,
            $this->logger
        );
        $app->run();
    }

    public function testRunActionGenerateKey()
    {
        $this->arguments->method("getAction")
                        ->willReturn("generate-key");

        $this->master_secret->expects($this->once())
                            ->method("generate");

        $app = new ClientApp(
            $this->arguments,
            $this->master_secret,
            $this->logger
        );
        $app->run();
    }
}
