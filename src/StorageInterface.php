<?php

declare(strict_types=1);

namespace WRS;

interface StorageInterface
{
    public function save(string $name, string $content);
    public function load(string $name);
}
