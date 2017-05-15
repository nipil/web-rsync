<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Apps\ServerApp;

class ServerAppTest extends TestCase
{
    public function testConstructor()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $app = new ServerApp($logger);
        $this->assertSame($logger, $app->getLogger(), "logger");
        return $app;
    }

    /**
     * @depends testConstructor
     */
    public function testRun($app)
    {
        $app->run();
        $this->assertTrue(true);
    }
}
