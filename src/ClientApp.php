<?php

declare(strict_types=1);

namespace WRS;

class ClientApp {

    private $logger;
    private $args;

    public function __construct() {
        $this->logger = \Logger::getLogger(get_class($this));
        $this->logger->debug(__METHOD__);
        $this->args = new Arguments();
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running client");

        $action_name = $this->args->get_action();
        if ($action_name === NULL) {
            $this->logger->fatal("No action provided");
            return;
        }

        $action = ActionFactory::create($action_name, $this->args);
        $action->run();
    }
}
