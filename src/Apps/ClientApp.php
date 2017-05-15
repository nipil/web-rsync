<?php

declare(strict_types=1);

namespace WRS\Apps;

use Psr\Log\LoggerInterface;

use WRS\Apps\Abstracts\App;

use WRS\Actions\ActionGenerateKey;

use WRS\Crypto\Interfaces\HashInterface;
use WRS\Crypto\Interfaces\RandomDataInterface;
use WRS\Crypto\Interfaces\KeyDerivatorInterface;

use WRS\Crypto\MasterSecret;

use WRS\KeyValue\Interfaces\KeyValueInterface;

use WRS\Arguments;

class ClientApp extends App
{
    private $arguments;
    private $master_secret;

    public function __construct(
        Arguments $arguments,
        MasterSecret $master_secret,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);

        $this->arguments = $arguments;
        $this->master_secret = $master_secret;
    }

    public function run()
    {
        $this->getLogger()->info("Running client");

        // parse arguments
        $this->arguments->parseArgs();

        // get action
        $action_name = $this->arguments->getAction();
        if ($action_name === null) {
            throw new \Exception("No action provided");
        }

        // act
        if ($action_name == "generate-key") {
            $action = new ActionGenerateKey(
                $this->master_secret,
                $this->getLogger()
            );
            $action->run();
        } else {
            throw new \Exception(sprintf("Unknown action : %s", $action_name));
        }

        return 0;
    }
}
