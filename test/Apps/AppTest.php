<?php

declare(strict_types=1);

namespace WRS\Tests\Apps;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Apps\Abstracts\App;

class AppTest extends TestCase
{
    public function testConstructor()
    {
        $directory = __DIR__;
        $logger = $this->createMock(LoggerInterface::class);
        $app = $this->getMockBuilder(App::class)
                    ->setConstructorArgs([$directory, $logger])
                    ->getMockForAbstractClass();

        $this->assertSame($directory, $app->getBasePath(), "base path");
        $this->assertSame($logger, $app->getLogger(), "logger");
    }
}
