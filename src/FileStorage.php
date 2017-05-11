<?php

declare(strict_types=1);

namespace WRS;

class FileStorage implements StorageInterface
{
    private $directory;

    public function __construct(string $directory) {
        $this->directory = $directory;
    }

    protected function get_path(string $name) {
        return $this->directory . DIRECTORY_SEPARATOR . $name;
    }

    public function save(string $name, string $content) {
        $filepath = $this->get_path($name);
        $result = @file_put_contents($filepath, $content);
        if ($result === FALSE) {
            throw new \Exception(sprintf(
                "Cannot save data to file %s",
                $filepath));
        }
    }

    public function load(string $name) {
        $filepath = $this->get_path($name);
        $result = @file_get_contents($filepath);
        if ($result === FALSE) {
            throw new \Exception(sprintf(
                "Cannot get content of file %s",
                $filepath));
        }
        return $result;
    }
}
