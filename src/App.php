<?php

declare(strict_types=1);

namespace WRS;

abstract class App {

    private $base_path;

    public function __construct(string $base_path) {
        $this->base_path = $base_path;
        $this->check_base_path();
    }

    public function get_base_path() {
        return $this->base_path;
    }

    public function  check_base_path() {
        if (realpath($this->base_path) === FALSE) {
            throw new \Exception(sprintf(
                "%s is not a valid path",
                $this->base_path));
        }
    }

    abstract public function run();
}
