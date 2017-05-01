<?php

declare(strict_types=1);

namespace WRS;

class ActionFactory {

    private static $logger = NULL;

    public static function setup_logger() {
        if (self::$logger === NULL) {
            self::$logger = \Logger::getLogger("ActionFactory");
            self::$logger->debug(__METHOD__);
        }
    }

    public static function create(string $name, Arguments $args) {
        self::setup_logger();
        self::$logger->debug(__METHOD__);

        if ($name == "createkey") {
            return new ActionCreateKey($args);
        } else {
            throw new \Exception(sprintf("Unknown action %s", $name));
        }
    }
}
