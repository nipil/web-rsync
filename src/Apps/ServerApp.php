<?php

declare(strict_types=1);

namespace WRS\Apps;

use Psr\Log\LoggerInterface;

use WRS\Apps\Abstracts\App;

class ServerApp extends App
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    public function run()
    {
        $this->getLogger()->info("Starting server");
    }
}
