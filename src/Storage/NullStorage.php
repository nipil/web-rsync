<?php

declare(strict_types=1);

namespace WRS\Storage;

use WRS\Storage\Interfaces\StorageInterface;

class NullStorage implements StorageInterface
{
    public function save(string $name, string $content)
    {
        return;
    }

    public function load(string $name)
    {
        throw new \UnderflowException(sprintf("Cannot load key %s", $name));
    }

    public function exists(string $name)
    {
        return false;
    }
}
