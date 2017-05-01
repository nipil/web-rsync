<?php

declare(strict_types=1);

namespace WRS;

class ActionCreateKey extends Action {

    private $logger;

    public function __construct(Arguments $args) {
        $this->logger = \Logger::getLogger(get_class($this));
        $this->logger->debug(__METHOD__);
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running action" . __CLASS__);
    }
}
