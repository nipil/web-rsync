<?php

declare(strict_types=1);

namespace WRS\Apps\Abstracts;

use Psr\Log\LoggerInterface;

abstract class App
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    abstract public function run();
}
