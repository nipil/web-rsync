<?php

declare(strict_types=1);

namespace WRS\Storage;

interface StorageInterface
{
    public function save(string $name, string $content);
    public function load(string $name);
    public function exists(string $name);
}
