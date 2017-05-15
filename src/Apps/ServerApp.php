<?php

declare(strict_types=1);

namespace WRS\Apps;

use Psr\Log\LoggerInterface;

use WRS\Apps\Abstracts\App;

class ServerApp extends App
{
    public function __construct(string $base_path, LoggerInterface $logger)
    {
        parent::__construct($base_path, $logger);
        $this->getLogger()->info("Starting server");
    }

    public function run()
    {
        $this->getLogger()->info("Starting server");
    }
}
