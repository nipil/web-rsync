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

    public function load_default_optional() {
        $this->logger->debug(__METHOD__);
        // check path alone
        $path = realpath($this->base_path);
        if ($path === FALSE) {
            throw new \Exception(sprintf(
                "Invalid path %s",
                $this->base_path));
        }
        // build config file path
        $filepath = $path . "/" . self::CONFIG_FILE;
        // load data
        $data = @include($file);
        // which may fail
        if ($data === FALSE) {
            $this->data = array();
        } else {
            $this->data = $data;
        }
        $this->logger->debug("Default config loaded: ".var_export($this->data, TRUE));
    }

    public function load_custom_required(string $filepath) {
        $this->logger->debug(__METHOD__.":".join(" ",func_get_args()));
        // file MUST exist
        $path = realpath($filepath);
        if ($path === FALSE) {
            throw new \Exception(sprintf(
                "Invalid path for config file %s",
                $path));
        }
        $data = @include($path);
        if ($data === FALSE) {
            throw new \Exception(sprintf(
                "Could not load configuration file %s",
                $path));
        }
        // store loaded data
        $this->data = $data;
        $this->logger->debug("Custom config loaded: ".var_export($this->data, TRUE));
    }

    public function get(string $name) {
        $this->logger->debug(__METHOD__.":".join(" ",func_get_args()));
        if (!isset($this->data[$name])) {
            throw new \Exception(sprintf(
                "Could not find configuration for value %s",
                $name));
        }
        return $this->data[$name];
    }
}
