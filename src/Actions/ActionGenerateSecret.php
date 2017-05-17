<?php

declare(strict_types=1);

namespace WRS\Actions;

use Psr\Log\LoggerInterface;

use WRS\Actions\Abstracts\Action;
use WRS\Crypto\MasterSecret;
use WRS\Utils\Arguments;

class ActionGenerateSecret extends Action
{
    private $master_secret;
    private $arguments;

    public function __construct(
        Arguments $arguments,
        MasterSecret $master_secret,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->master_secret = $master_secret;
        $this->arguments = $arguments;
    }

    public function run()
    {
        $this->getLogger()->info("Generating master secret");
        $key_length = $this->arguments->getCommandOption("key_length");
        $salt_length = $this->arguments->getCommandOption("salt_length");
        $this->master_secret->generate($key_length, $salt_length);
    }
}
