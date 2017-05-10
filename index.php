<?php

require_once __DIR__.'/vendor/autoload.php';

use WRS\App;
use WRS\ClientApp;
use WRS\ServerApp;

try {
    // configure logger
    App::SetLogger(new \Monolog\Logger('web-rsync'));
    // same script handles client side and server side
    if (php_sapi_name() === 'cli' OR defined('STDIN')) {
        $app = new ClientApp(__DIR__);
        $result = $app->run();
        exit($result);
    } else {
        $app = new ServerApp(__DIR__);
        $app->run();
    }
} catch (\Exception $e) {
    $main_logger = App::GetLogger("main");
    $main_logger->error($e);
}
