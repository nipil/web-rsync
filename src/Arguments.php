<?php

declare(strict_types=1);

namespace WRS;

use WRS\Apps\App;

class Arguments
{
    const ACTION = "action";

    public function __construct(array $forced_arguments = null)
    {
        $this->args = array();
    }

    public function parse()
    {
        $this->args = getopt(
            "",
            array(
                self::ACTION.":",
            )
        );
    }

    public function getParam(string $name)
    {
        if (isset($this->args[$name])) {
            return $this->args[$name];
        } else {
            return null;
        }
    }

    public function getAction()
    {
        return $this->getParam(self::ACTION);
    }

    /* these following methods are used only for testing */

    public function setArguments(array $new_arguments)
    {
        $this->args = $new_arguments;
    }

    public function getArguments()
    {
        return $this->args;
    }
}
