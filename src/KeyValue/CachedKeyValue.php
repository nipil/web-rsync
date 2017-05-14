<?php

declare(strict_types=1);

namespace WRS\KeyValue;

use WRS\KeyValue\Interfaces\KeyValueInterface;

class CachedKeyValue implements KeyValueInterface {

    private $source;
    private $cache;

    public function __construct(KeyValueInterface $source) {
        $this->source = $source;
        $this->cache = array();
    }

    public function has_cache(string $key) {
        return array_key_exists($key, $this->cache);
    }

    public function get_cache(string $key) {
        if (!$this->has_cache($key)) {
            throw new \Exception(sprintf("Key not found in cache : %s", $key));
        }
        return $this->cache[$key];
    }

    public function set_cache_string(string $key, string $value) {
        $this->cache[$key] = $value;
    }

    public function set_cache_integer(string $key, int $value) {
        $this->cache[$key] = $value;
    }

    public function has_key(string $key) {
        if ($this->has_cache($key)) {
            return TRUE;
        }
        return $this->source->has_key($key);
    }

    public function get_string(string $key) {
        if ($this->has_cache($key)) {
            $value = $this->cache[$key];
            if (!is_string($value)) {
                throw new \Exception(sprintf("Invalid type for key: %s", $key));
            }
            return $value;
        } else {
            $value = $this->source->get_string($key);
            $this->cache[$key] = $value;
            return $value;
        }
    }

    public function set_string(string $key, string $value) {
        $this->set_cache_string($key, $value);
        $this->source->set_string($key, $value);
    }

    public function get_integer(string $key) {
        if ($this->has_cache($key)) {
            $value = $this->cache[$key];
            if (!is_int($value)) {
                throw new \Exception(sprintf("Invalid type for key: %s", $key));
            }
            return $value;
        } else {
            $value = $this->source->get_integer($key);
            $this->cache[$key] = $value;
            return $value;
        }
    }

    public function set_integer(string $key, int $value) {
        $this->set_cache_integer($key, $value);
        $this->source->set_integer($key, $value);
    }
}
