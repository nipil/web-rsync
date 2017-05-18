<?php

declare(strict_types=1);

namespace WRS\KeyValue;

use WRS\KeyValue\Interfaces\KeyValueInterface;

class CachedKeyValue implements KeyValueInterface
{

    private $source;
    private $cache;

    public function __construct(KeyValueInterface $source)
    {
        $this->source = $source;
        $this->cache = array();
    }

    public function hasCache(string $key)
    {
        return array_key_exists($key, $this->cache);
    }

    public function getCache(string $key)
    {
        if (!$this->hasCache($key)) {
            throw new \RuntimeException(sprintf("Key not found in cache : %s", $key));
        }
        return $this->cache[$key];
    }

    public function setCacheString(string $key, string $value)
    {
        $this->cache[$key] = $value;
    }

    public function setCacheInteger(string $key, int $value)
    {
        $this->cache[$key] = $value;
    }

    public function hasKey(string $key)
    {
        if ($this->hasCache($key)) {
            return true;
        }
        return $this->source->hasKey($key);
    }

    public function getString(string $key)
    {
        if ($this->hasCache($key)) {
            $value = $this->cache[$key];
            if (!is_string($value)) {
                throw new \DomainException(sprintf("Invalid type for key: %s", $key));
            }
            return $value;
        } else {
            $value = $this->source->getString($key);
            $this->cache[$key] = $value;
            return $value;
        }
    }

    public function setString(string $key, string $value)
    {
        $this->setCacheString($key, $value);
        $this->source->setString($key, $value);
    }

    public function getInteger(string $key)
    {
        if ($this->hasCache($key)) {
            $value = $this->cache[$key];
            if (!is_int($value)) {
                throw new \DomainException(sprintf("Invalid type for key: %s", $key));
            }
            return $value;
        } else {
            $value = $this->source->getInteger($key);
            $this->cache[$key] = $value;
            return $value;
        }
    }

    public function setInteger(string $key, int $value)
    {
        $this->setCacheInteger($key, $value);
        $this->source->setInteger($key, $value);
    }
}
