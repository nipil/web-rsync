<?php

declare(strict_types=1);

namespace WRS\Apps;

class ServerApp extends App {

    private $logger;
    private $base_path;

    public function __construct(string $base_path) {
        parent::__construct($base_path);

        $this->logger = App::GetLogger();
        $this->logger->debug(__METHOD__);

        $this->logger->info("Starting server");
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running server");
    }
}
