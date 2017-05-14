<?php

declare(strict_types=1);

namespace WRS\Crypto\Interfaces;

interface SecretKeeperInterface {
    public function set_key(string $key);
    public function set_salt(string $salt);
    public function get_key();
    public function get_salt();
}
