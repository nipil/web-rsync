<?php

declare(strict_types=1);

namespace WRS\KeyValue;

use WRS\Utils,
    WRS\Apps\Abstracts\App,
    WRS\KeyValue\Interfaces\KeyValueInterface,
    WRS\Storage\Interfaces\StorageInterface;

class StoredKeyValue implements KeyValueInterface {

    private $logger;
    private $storage;

    public function __construct(StorageInterface $storage) {
        $this->logger = App::GetLogger();
        $this->storage = $storage;
    }

    public function has_key(string $key) {
        return $this->storage->exists($key);
    }

    public function get_string(string $key) {
        return $this->storage->load($key);
    }

    public function set_string(string $key, string $value) {
        $this->storage->save($key, $value);
    }

    public function get_integer(string $key) {
        $str = $this->storage->load($key);
        $int = Utils::StringToInt($str);
        return $int;
    }

    public function set_integer(string $key, int $value) {
        $str = sprintf("%d", $value);
        $this->storage->save($key, $str);
    }
}
