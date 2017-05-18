<?php

declare(strict_types=1);

namespace WRS\Apps;

use Psr\Log\LoggerInterface;

use WRS\Apps\Abstracts\App;

use WRS\Actions\ActionGenerateSecret;

use WRS\Crypto\Interfaces\HashInterface;
use WRS\Crypto\Interfaces\RandomDataInterface;
use WRS\Crypto\Interfaces\KeyDerivatorInterface;

use WRS\Crypto\MasterSecret;

use WRS\Exceptions\WrsException;

use WRS\KeyValue\Interfaces\KeyValueInterface;

use WRS\Utils\Arguments;

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
        $this->arguments->parse();

        // get command name
        $command_name = $this->arguments->getCommand();
        if ($command_name === null) {
            throw new WrsException("No command provided");
        }

        // act
        if ($command_name == "generate-secret") {
            $action = new ActionGenerateSecret(
                $this->arguments,
                $this->master_secret,
                $this->getLogger()
            );
            $action->run();
        } else {
            throw new WrsException(sprintf("Unknown command : %s", $command_name));
        }

        return 0;
    }
}
