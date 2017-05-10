<?php

declare(strict_types=1);

namespace WRS;

abstract class App {

    private static $Logger = NULL;

    public static function SetLogger(\Psr\Log\LoggerInterface $logger) {
        if ($logger === NULL) {
            throw new \Exception("Invalid logger");
        }
        self::$Logger = $logger;
    }

    public static function GetLogger() {
        if (self::$Logger === NULL) {
            self::SetLogger(new \Psr\Log\NullLogger());
        }
        return self::$Logger;
    }

    private $base_path;

    public function __construct(string $base_path) {
        $this->base_path = $base_path;
    }

    public function get_base_path() {
        return $this->base_path;
    }

    abstract public function run();
}
