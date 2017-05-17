<?php

declare(strict_types=1);

namespace WRS\Utils;

use \Console_CommandLine;

class Arguments
{
    private $parser;
    private $result;
    private $constructor_argv;

    public function __construct(array $constructor_argv = null)
    {
        $this->constructor_argv = $constructor_argv;
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

    public function parse(array $local_argv = null)
    {
        $final_argc = null;
        $final_argv = null;

        if ($local_argv !== null) {
            $final_argv = $local_argv;
            $final_argc = count($local_argv);
        } elseif ($this->constructor_argv !== null) {
            $final_argv = $this->constructor_argv;
            $final_argc = count($this->constructor_argv);
        }

        if ($final_argv !== null && $final_argc == 0) {
            throw new \Exception("Argument list must start with program name");
        }

        $this->result = $this->parser->parse($final_argc, $final_argv);
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
