<?php

declare(strict_types=1);

namespace WRS;

abstract class App {

    private $base_path;

    public function __construct(string $base_path) {
        $this->base_path = $base_path;
    }

    public function get_base_path() {
        return $this->base_path;
    }

    abstract public function run();
}
