<?php

declare(strict_types=1);

namespace WRS\Storage;

use WRS\Storage\Interfaces\StorageInterface;

class FileStorage implements StorageInterface
{
    private $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    protected function getPath(string $name)
    {
        return $this->directory . DIRECTORY_SEPARATOR . $name;
    }

    public function createDirectoryIfNotExists()
    {
        if (file_exists($this->directory)) {
            if (is_dir($this->directory)) {
                return false;
            }
            throw new \Exception(sprintf("Path is not a directory : %s", $this->directory));
        }
        $result = mkdir($this->directory, 0750, true);
        if ($result === false) {
            throw new \Exception(sprintf("Could not create directory : %s", $this->directory));
        }
        return true;
    }

    public function save(string $name, string $content)
    {
        $filepath = $this->getPath($name);
        $result = @file_put_contents($filepath, $content);
        if ($result === false) {
            throw new \Exception(sprintf("Cannot save data to file %s", $filepath));
        }
    }

    public function load(string $name)
    {
        $filepath = $this->getPath($name);
        $result = @file_get_contents($filepath);
        if ($result === false) {
            throw new \Exception(sprintf("Cannot get content of file %s", $filepath));
        }
        return $result;
    }

    public function exists(string $name)
    {
        $filepath = $this->getPath($name);
        return is_file($filepath);
    }
}
