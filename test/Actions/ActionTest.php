<?php

declare(strict_types=1);

namespace WRS\Tests\Actions;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Actions\Abstracts\Action;

class ActionTest extends TestCase
{
    public function testConstructor()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $action = $this->getMockBuilder(Action::class)
                       ->setConstructorArgs([$logger])
                       ->getMockForAbstractClass();
        $this->assertSame($logger, $action->getLogger());
    }
}
