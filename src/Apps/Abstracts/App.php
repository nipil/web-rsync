<?php

declare(strict_types=1);

namespace WRS\Apps\Abstracts;

use Psr\Log\LoggerInterface;

abstract class App
{
    private $logger;

    private $base_path;

    public function __construct(string $base_path, LoggerInterface $logger)
    {
        $this->base_path = $base_path;
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getBasePath()
    {
        return $this->base_path;
    }

    abstract public function run();
}
