<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Apps\Abstracts\App;

class AppTest extends TestCase
{
    private $logger;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testConstructor()
    {
        $app = $this->getMockBuilder(App::class)
            ->setConstructorArgs([$this->logger])
            ->getMockForAbstractClass();

        $this->assertSame($this->logger, $app->getLogger());
    }
}
