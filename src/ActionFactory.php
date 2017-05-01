<?php

declare(strict_types=1);

namespace WRS;

class ActionFactory {

    private static $logger = NULL;

    public static function setup_logger() {
        if (ActionFactory::$logger === NULL) {
            ActionFactory::$logger = \Logger::getLogger("ActionFactory");
            ActionFactory::$logger->debug(__METHOD__);
        }
    }

    public static function create(string $name, Arguments $args) {
        ActionFactory::setup_logger();
        ActionFactory::$logger->debug(__METHOD__);

        if ($name == "createkey") {
            return new ActionCreateKey($args);
        } else {
            throw new \Exception("Unknown action ".$name);
        }
    }
}
