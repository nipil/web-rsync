<?php

declare(strict_types=1);

namespace WRS;

use WRS\Apps\App;

class Arguments
{
    private $logger;
    private $args;

    public function __construct()
    {
        $this->logger = App::GetLogger();
        $this->logger->debug(__METHOD__);
        $this->args = array();
    }

    public function parseArgs()
    {
        $this->logger->debug(__METHOD__);
        $this->args = getopt("", array(
            "action:",
            "config:",
            ));
        $this->logger->debug($this->args);
    }

    protected function getParam(string $name)
    {
        $this->logger->debug(__METHOD__, func_get_args());
        if (isset($this->args[$name])) {
            return $this->args[$name];
        } else {
            return null;
        }
    }

    public function getAction()
    {
        $this->logger->debug(__METHOD__);
        return $this->get_param("action");
    }

    public function getConfig()
    {
        $this->logger->debug(__METHOD__);
        return $this->get_param("config");
    }
}
