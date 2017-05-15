<?php

declare(strict_types=1);

namespace WRS\KeyValue\Interfaces;

interface KeyValueInterface
{
    public function hasKey(string $key);
    public function getInteger(string $key);
    public function setInteger(string $key, int $value);
    public function getString(string $key);
    public function setString(string $key, string $value);
}
