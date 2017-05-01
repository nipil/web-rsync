<?php

require_once __DIR__.'/vendor/autoload.php';

use WRS\ClientApp;
use WRS\ServerApp;

\Logger::configure(
    // TODO
);

if (php_sapi_name() === 'cli' OR defined('STDIN')) {
    $app = new ClientApp();
} else {
    $app = new ServerApp();
}

$app->run();
