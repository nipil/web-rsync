<?php

declare(strict_types=1);

namespace WRS\Tests\Actions;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Actions\ActionGenerateSecret;
use WRS\Crypto\MasterSecret;
use WRS\Utils\Arguments;

class ActionGenerateSecretTest extends TestCase
{
    private $logger;
    private $secret;
    private $arguments;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->secret = $this->createMock(MasterSecret::class);
        $this->arguments = $this->createMock(Arguments::class);
    }

    public function testRun()
    {
        $this->arguments->expects($this->exactly(2))
            ->method("getCommandOption")
            ->withConsecutive(
                ["key_length"],
                ["salt_length"]
            )
            ->will($this->onConsecutiveCalls(
                20,
                10
            ));

        $this->secret->expects($this->once())
            ->method("generate")
            ->with(
                $this->identicalTo(20),
                $this->identicalTo(10)
            );

        $sut = new ActionGenerateSecret($this->arguments, $this->secret, $this->logger);
        $sut->run();
    }
}
