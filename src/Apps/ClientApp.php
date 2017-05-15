<?php

declare(strict_types=1);

namespace WRS\Apps;

use WRS\Arguments;
use WRS\Apps\App;
use WRS\Crypto\KeyManager;
use WRS\KeyValue\StoredKeyValue;
use WRS\Storage\FileStorage;

class ClientApp extends App
{
    private $logger;
    private $args;
    private $key_manager;

    public function __construct(string $base_path)
    {
        parent::__construct($base_path);

        $this->logger = App::GetLogger();
        $this->logger->debug(__METHOD__, func_get_args());

        $this->args = new Arguments();
        $this->config = new StoredKeyValue($this->get_base_path());
        $this->key_manager = new KeyManager($this->get_base_path());
    }

    public function run()
    {
        $this->logger->debug(__METHOD__);
        $this->logger->info("Running client");

        // parse arguments
        $this->args->parse_args();

        // load configuration
        $config = $this->args->get_config();
        if ($config === null) {
            $this->config->load_default_optional();
        } else {
            $this->config->load_custom_required($config);
        }

        // get action
        $action_name = $this->args->get_action();
        if ($action_name === null) {
            throw new \Exception("No action provided");
        }

        // act
        if ($action_name == "createkey") {
            $action_create_key = new ActionCreateKey($this->args, $this->key_manager);
            $action_create_key->run();
        } else {
            throw new \Exception("Unknown action");
        }

        return 0;
    }
}
