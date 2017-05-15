<?php

declare(strict_types=1);

namespace WRS\Actions;

use Psr\Log\LoggerInterface;

use WRS\Actions\Abstracts\Action;
use WRS\Crypto\MasterSecret;

class ActionGenerateKey extends Action
{
    private $master_secret;

    public function __construct(
        MasterSecret $master_secret,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->master_secret = $master_secret;
    }

    public function run()
    {
        $this->getLogger()->info("Generating master secret");
        $this->master_secret->generate();
    }
}
