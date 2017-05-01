<?php

declare(strict_types=1);

namespace WRS;

class ClientApp {

    private $logger;

    public function __construct() {
        $this->logger = \Logger::getLogger(get_class($this));
        $this->logger->info("Starting client");
    }

    public function run() {
        $this->logger->info("Running client");
    }
}
