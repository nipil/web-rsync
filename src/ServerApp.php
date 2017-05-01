<?php

declare(strict_types=1);

namespace WRS;

class ServerApp {

    private $logger;

    public function __construct() {
        $this->logger = \Logger::getLogger(get_class($this));
        $this->logger->debug(__METHOD__);
        $this->logger->info("Starting server");
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running server");
    }
}
