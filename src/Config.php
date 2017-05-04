<?php

declare(strict_types=1);

namespace WRS;

class Config {

    const CONFIG_FILE = "wrs_config.php";

    private $logger;
    private $base_path;
    private $data;

    public function __construct(bool $file_required, string $base_path) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__);
        $this->base_path = $base_path;
        $this->data = array();
    }

    public function load_required_default() {
        $path = realpath($this->base_path . "/" . self::CONFIG_FILE);
        if ($path === FALSE) {
            throw new \Exception(sprintf(
                "Invalid path for file %s in folder %s",
                self::CONFIG_FILE,
                $this->base_path));
        }
        $data = @include($path);
        if ($data === FALSE) {
            throw new \Exception(sprintf(
                "Could not load configuration file %s",
                self::CONFIG_FILE));
        }
        $this->data = $data;
    }

    public function load_optional_custom(string $filepath) {
        $path = realpath($filepath);
        if ($path === FALSE) {
            throw new \Exception(sprintf(
                "Invalid path for config file %s",
                filepath));
        }
        $this->data = @include($path);
        if ($data === FALSE) {
        }
        $this->data = array();
    }

    public function get(string $name) {
        if (!isset($this->data[$name])) {
            throw new \Exception(sprintf(
                "Could not find configuration for value %s",
                $name));
        }
        return $this->data[$name];
    }
}
