<?php

declare(strict_types=1);

namespace WRS\KeyValue;

use WRS\Utils;
use WRS\Apps\Abstracts\App;
use WRS\KeyValue\Interfaces\KeyValueInterface;
use WRS\Storage\Interfaces\StorageInterface;
use WRS\Utils\StringToInt;

class StoredKeyValue implements KeyValueInterface
{
    private $logger;
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->logger = App::GetLogger();
        $this->storage = $storage;
    }

    public function hasKey(string $key)
    {
        return $this->storage->exists($key);
    }

    public function getString(string $key)
    {
        return $this->storage->load($key);
    }

    public function setString(string $key, string $value)
    {
        $this->storage->save($key, $value);
    }

    public function getInteger(string $key)
    {
        $str = $this->storage->load($key);
        $int = StringToInt::convert($str);
        return $int;
    }

    public function setInteger(string $key, int $value)
    {
        $str = sprintf("%d", $value);
        $this->storage->save($key, $str);
    }
}
