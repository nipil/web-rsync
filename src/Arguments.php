<?php

declare(strict_types=1);

namespace WRS;

class Arguments {

    private $args;

    public function __construct() {
        $this->logger = \Logger::getLogger(get_class($this));
        $this->logger->debug(__METHOD__);
        $this->args = NULL;
        $this->parse_args();
    }

    protected function parse_args() {
        $this->logger->debug(__METHOD__);
        $this->args = getopt("", array(
            "action:",
            ));
        $this->logger->debug($this->args);
    }

    protected function get_param(string $name) {
        $this->logger->debug(__METHOD__);
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
}
