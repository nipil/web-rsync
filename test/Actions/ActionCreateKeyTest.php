<?php

declare(strict_types=1);

namespace WRS\Tests\Actions;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use WRS\Crypto\MasterSecret;

use WRS\Actions\ActionCreateKey;

class ActionCreateKeyTest extends TestCase
{
    public function testCreateKey()
    {
        $logger = $this->createMock(LoggerInterface::class);

        $master_secret = $this->createMock(MasterSecret::class);
        $master_secret->expects($this->once())
                      ->method("generate");

        $sut = new ActionCreateKey($master_secret, $logger);
        $sut->run();
    }
}
