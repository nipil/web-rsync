<?php

declare(strict_types=1);

namespace WRS\Utils;

use \Console_CommandLine;

class Arguments
{
    private $parser;
    private $result;

    public function __construct()
    {
        $this->result = null;
        $this->parser = new Console_CommandLine();

        $genkey = $this->parser->addCommand(
            'generate-secret',
            array(
                'description' => 'generate a master secret for use on both sides'
            )
        );

        $genkey->addOption(
            "key_length",
            array(
                "short_name" => "-k",
                "long_name" => "--key-length",
                "help_name" => "LEN",
                "description" => "length of the master key, in bytes",
                "default" => 1024,
                "action" => "StoreInt"
            )
        );

        $genkey->addOption(
            "salt_length",
            array(
                "short_name" => "-s",
                "long_name" => "--salt-length",
                "help_name" => "LEN",
                "description" => "length of the master salt, in bytes",
                "default" => 16,
                "action" => "StoreInt"
            )
        );
    }

    public function parse(array $custom_argv = null)
    {
        $custom_argc = null;

        if ($custom_argv !== null) {
            // prepend a program name
            array_unshift($custom_argv, "program_name");
            $custom_argc = count($custom_argv);
        }

        $this->result = $this->parser->parse($custom_argc, $custom_argv);
    }

    public function validateResult()
    {
        if ($this->result === null) {
            throw new \Exception("Arguments not yet parsed");
        }
    }

    public function getCommand()
    {
        $this->validateResult();
        if ($this->result->command_name === false) {
            return null;
        }
        return $this->result->command_name;
    }

    public function validateCommand()
    {
        $this->validateResult();
        if ($this->getCommand() === null) {
            throw new \Exception("No command provided");
        }
    }

    public function getCommandOption(string $name)
    {
        $this->validateCommand();
        if (!array_key_exists($name, $this->result->command->options)) {
            throw new \Exception(sprintf("Command option %s is not defined", $name));
        }
        return $this->result->command->options[$name];
    }
}
