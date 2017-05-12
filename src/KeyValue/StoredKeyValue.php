<?php

declare(strict_types=1);

namespace WRS\KeyValue;

use WRS\Apps\App, WRS\Storage\StorageInterface;

class StoredKeyValue implements KeyValueInterface {

    private $logger;
    private $storage;

    public static function StringToInt(string $input) {
        $valid = preg_match('/^-?[[:digit:]]+$/', $input);
        if ($valid === 0) {
            throw new \Exception(sprintf(
                "Invalid integer %s",
                $input));
        }
        $result = sscanf($input, "%d", $value);
        if ($result === 0) {
            throw new \Exception(sprintf(
                "Cannot convert %s to an integer",
                $name));
        }
        return $value;
    }

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
        $int = self::StringToInt($str);
        return $int;
    }

    public function set_integer(string $key, int $value) {
        $str = sprintf("%d", $value);
        $this->storage->save($key, $str);
    }
}
