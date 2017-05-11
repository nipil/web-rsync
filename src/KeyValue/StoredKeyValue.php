<?php

declare(strict_types=1);

namespace WRS\KeyValue;

use WRS\Apps\App, WRS\Storage\StorageInterface;

class StoredKeyValue implements KeyValueInterface {

    private $logger;
    private $storage;
    private $data;
    private $definitions;

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
        $this->logger->debug(__METHOD__, func_get_args());
        $this->storage = $storage;
        $this->data = array();
    }

    public function has_key(string $key) {
        return array_key_exists($key, $this->data);
    }

    public function get_string(string $key) {
        if (!$this->has_key($key)) {
            $this->data[$key] = $this->storage->load($key);
        }
        return $this->data[$key];
    }

    public function set_string(string $key, string $value) {
        $this->storage->save($key, $value);
        $this->data[$key] = $value;
    }

    public function get_integer(string $key) {
        if (!$this->has_key($key)) {
            $input = $this->storage->load($key);
            $this->data[$key] = self::StringToInt($input);
        }
        return $this->data[$key];
    }

    public function set_integer(string $key, int $value) {
        $this->storage->save($key, sprintf("%d", $value));
        $this->data[$key] = $value;
    }
}
