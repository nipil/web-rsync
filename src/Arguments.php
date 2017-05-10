<?php

declare(strict_types=1);

namespace WRS;

class Arguments {

    private $logger;
    private $args;

    public function __construct() {
        $this->logger = App::GetLogger(__CLASS__);
        $this->logger->debug(__METHOD__);
        $this->args = array();
    }

    public function parse_args() {
        $this->logger->debug(__METHOD__);
        $this->args = getopt("", array(
            "action:",
            "config:",
            ));
        $this->logger->debug($this->args);
    }

    protected function get_param(string $name) {
        $this->logger->debug(__METHOD__, func_get_args());
        if (isset($this->args[$name])) {
            return $this->args[$name];
        } else {
            return NULL;
        }
    }

    public function get_action() {
        $this->logger->debug(__METHOD__);
        return $this->get_param("action");
    }

    public function get_config() {
        $this->logger->debug(__METHOD__);
        return $this->get_param("config");
    }
}
