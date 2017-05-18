<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Apps\ServerApp;

class ServerAppTest extends TestCase
{
    private $logger;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testRun()
    {
        $app = new ServerApp($this->logger);
        $this->assertSame(0, $app->run());
    }
}
