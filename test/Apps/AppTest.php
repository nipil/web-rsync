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
        $logger = $this->createMock(LoggerInterface::class);
        $app = $this->getMockBuilder(App::class)
                    ->setConstructorArgs([$logger])
                    ->getMockForAbstractClass();
        $this->assertSame($logger, $app->getLogger());
    }
}
