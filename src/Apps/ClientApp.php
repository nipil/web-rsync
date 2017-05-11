<?php

declare(strict_types=1);

namespace WRS\Apps;

use WRS\Arguments,
    WRS\Apps\App,
    WRS\Crypto\KeyManager,
    WRS\KeyValue\StoredKeyValue,
    WRS\Storage\FileStorage;

class ClientApp extends App {

    private $logger;
    private $args;
    private $key_manager;

    public function __construct(string $base_path) {
        parent::__construct($base_path);

        $this->logger = App::GetLogger();
        $this->logger->debug(__METHOD__, func_get_args());

        $this->args = new Arguments();
        $this->config = new StoredKeyValue($this->get_base_path());
        $this->key_manager = new KeyManager($this->get_base_path());
    }

    public function run() {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running client");

        // parse arguments
        $this->args->parse_args();

        // load configuration
        $config = $this->args->get_config();
        if ($config === NULL) {
            $this->config->load_default_optional();
        } else {
            $this->config->load_custom_required($config);
        }

        // get action
        $action_name = $this->args->get_action();
        if ($action_name === NULL) {
            throw new \Exception("No action provided");
        }

        // act
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
