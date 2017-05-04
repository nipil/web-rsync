<?php

declare(strict_types=1);

namespace WRS;

class ClientApp {

    private $logger;
    private $args;
    private $key_manager;
    private $base_path;

    public function __construct(string $base_path) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__);
        $this->base_path = $base_path;
        $this->config = new Config(FALSE, $base_path);
        $this->config->load_required_default();
        $this->args = new Arguments($this->config);
        $this->key_manager = new KeyManager($this->args);
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running client");

        $action_name = $this->args->get_action();
        if ($action_name === NULL) {
            throw new \Exception("No action provided");
        }

        if ($action_name == "createkey") {
            $action_create_key = new ActionCreateKey(
                $this->args,
                $this->key_manager);
            $action_create_key->run();
        } else {
            throw new \Exception("Unknown action");
        }
        return 0;
    }
}
