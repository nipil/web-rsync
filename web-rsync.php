<?php

require_once __DIR__.'/vendor/autoload.php';

use Monolog\Logger;

use WRS\Apps\ClientApp;
use WRS\Apps\ServerApp;

use WRS\Crypto\NativeHasher;
use WRS\Crypto\NativeRandomizer;
use WRS\Crypto\HmacKeyDerivator;
use WRS\Crypto\MasterSecret;

use WRS\KeyValue\CachedKeyValue;
use WRS\KeyValue\StoredKeyValue;

use WRS\Storage\FileStorage;

use WRS\Utils\Arguments;

try {

    // Common setup for client and server

    $hasher = new NativeHasher("sha512");

    $randomizer = new NativeRandomizer();

    $config_storage = new FileStorage(__DIR__ . DIRECTORY_SEPARATOR . "conf");
    $config_storage->createDirectoryIfNotExists();

    $config = new CachedKeyValue(new StoredKeyValue($config_storage));

    $master_secret = new MasterSecret(
        "master",
        $config,
        $randomizer
    );

    if (php_sapi_name() === 'cli' or defined('STDIN')) {

        // Client specific setup
        $arguments = new Arguments();
        $logger = new Logger('client');

        // Run client
        $app = new ClientApp(
            $arguments,
            $master_secret,
            $logger
        );
        $result = $app->run();

        // End processing after client has run
        exit($result);

    } else {

        // Server specific setup
        $logger = new Logger('server');

        // Run server-side
        $app = new ServerApp($logger);
        $app->run();

    }

} catch (\Exception $e) {
    trigger_error($e, E_USER_ERROR);
}
