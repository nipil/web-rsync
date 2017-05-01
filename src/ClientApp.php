<?php

declare(strict_types=1);

namespace WRS;

class ClientApp {

    private $logger;
    private $args;
    private $key_manager;

    public function __construct() {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__);
        $this->args = new Arguments();
        $this->key_manager = new KeyManager($this->args);
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running client");

        $action_name = $this->args->get_action();
        if ($action_name === NULL) {
            $this->logger->fatal("No action provided");
            return;
        }

        if ($action_name == "createkey") {
            $action_create_key = new ActionCreateKey(
                $this->args,
                $this->key_manager);
            $action_create_key->run();
        } else {
            throw new \Exception("Unknown action");
        }
    }
}
