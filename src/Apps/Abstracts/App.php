<?php

declare(strict_types=1);

namespace WRS\Apps\Abstracts;

abstract class App
{

    private static $Logger = null;

    public static function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        if ($logger === null) {
            throw new \Exception("Invalid logger");
        }
        self::$Logger = $logger;
    }

    public static function getLogger()
    {
        if (self::$Logger === null) {
            self::SetLogger(new \Psr\Log\NullLogger());
        }
        return self::$Logger;
    }

    private $base_path;

    public function __construct(string $base_path)
    {
        $this->base_path = $base_path;
    }

    public function getBasePath()
    {
        return $this->base_path;
    }

    abstract public function run();
}
