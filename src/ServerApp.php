<?php

declare(strict_types=1);

namespace WRS;

class ServerApp {

    private $logger;
    private $base_path;

    public function __construct(string $base_path) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__);
        $this->base_path = $base_path;
        $this->logger->info("Starting server");
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running server");
    }
}
