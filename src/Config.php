<?php

declare(strict_types=1);

namespace WRS;

class Config {

    const CONFIG_FILE = "wrs_config.php";

    private $logger;
    private $base_path;
    private $data;

    public function __construct(string $base_path) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__.":".join(" ",func_get_args()));
        $this->base_path = $base_path;
        $this->data = array();
    }

    public function get_data() {
        return $this->data;
    }

    public function load_default_optional() {
        $this->logger->debug(__METHOD__);
        // build config file path
        $filepath = $this->base_path . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
        if (!file_exists($filepath)) {
            $this->logger->info("No configuration file found, using default values");
            $this->data = array();
            return;
        }
        // load data
        $data = @include($filepath);
        // which may fail because
        if (gettype($data) !== "array") {
            throw new \Exception(sprintf(
                "Invalid configuration file %s",
                $filepath));
        }
        $this->data = $data;
        $this->logger->debug("Default config loaded: ".var_export($this->data, TRUE));
    }

    public function load_custom_required(string $filepath) {
        $this->logger->debug(__METHOD__.":".join(" ",func_get_args()));
        if (!file_exists($filepath)) {
            throw new \Exception(sprintf(
                "Configuration file %s not found",
                $filepath));
        }
        $data = @include($filepath);
        if (gettype($data) !== "array") {
            throw new \Exception(sprintf(
                "Invalid configuration file %s",
                $filepath));
        }
        // store loaded data
        $this->data = $data;
        $this->logger->debug("Custom config loaded: ".var_export($this->data, TRUE));
    }

    public function get(string $name) {
        $this->logger->debug(__METHOD__.":".join(" ",func_get_args()));
        if (!isset($this->data[$name])) {
            return NULL;
        }
        return $this->data[$name];
    }
}
