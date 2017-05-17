<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Utils\Arguments;
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
     * @expectedExceptionMessageRegExp #^No command provided$#
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
     * @expectedExceptionMessageRegExp #^Unknown command : .*$#
     */
    public function testRunUnknownAction()
    {
        $this->arguments->method("getCommand")
                        ->willReturn("this_is_an_unknown_action");

        $app = new ClientApp(
            $this->arguments,
            $this->master_secret,
            $this->logger
        );
        $app->run();
    }

    public function testRunActionGenerateSecret()
    {
        // This tests "too far"
        // But i cannot test new AGK()->run()

        $arguments = new Arguments(
            array(
                "program_name",
                "generate-secret",
                "-k",
                12,
                "-s",
                24
            )
        );

        $this->master_secret->expects($this->once())
            ->method("generate")
            ->with(
                $this->identicalTo(12),
                $this->identicalTo(24)
            );

        $app = new ClientApp(
            $arguments,
            $this->master_secret,
            $this->logger
        );
        $app->run();
    }
}
