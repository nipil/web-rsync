<?php

declare(strict_types=1);

namespace WRS\KeyValue\Interfaces;

interface KeyValueInterface {
    public function has_key(string $key);
    public function get_integer(string $key);
    public function set_integer(string $key, int $value);
    public function get_string(string $key);
    public function set_string(string $key, string $value);
}
