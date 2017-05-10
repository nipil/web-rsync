<?php

declare(strict_types=1);

namespace WRS;

use Monolog\Logger;

abstract class App {

    private static $Logger = NULL;

    public static function GetLogger(string $name) {
        if (self::$Logger === NULL) {
            self::$Logger = new Logger('root');
        }
        return self::$Logger->withName($name);
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
