<?php

declare(strict_types=1);

namespace WRS;

use PHPUnit\Framework\TestCase;

use org\bovigo\vfs\vfsStream, org\bovigo\vfs\vfsStreamWrapper, org\bovigo\vfs\vfsStreamDirectory;

class ConfigTest extends TestCase
{
    const SAMPLE_INPUT_VALID = "<?php" . PHP_EOL
        . "return array (" . PHP_EOL
        . "  'integer' => 42," . PHP_EOL
        . "  'string' => 'text'," . PHP_EOL
        . ");" . PHP_EOL;

    const SAMPLE_INPUT_NOT_AN_ARRAY = "toto";

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('baseDirectory'));
    }

    public function setup_config_content(string $content) {
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(Config::CONFIG_FILE),
            "Config file already present");
        file_put_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . Config::CONFIG_FILE),
            $content);
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(Config::CONFIG_FILE),
            "Config file is absent");
        $this->assertEquals(file_get_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . Config::CONFIG_FILE)),
            $content);
    }

    public function testConfigLoadDefaultOptionalWithoutFile() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(Config::CONFIG_FILE),
            "config file already present");
        $config->load_default_optional();
        $this->assertEquals($config->get_data(), array(), "Default configuration is incorrect");
    }

    public function testConfigLoadDefaultOptionalWithFile() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $this->setup_config_content(self::SAMPLE_INPUT_VALID);
        $config->load_default_optional();
        $this->assertEquals(
            $config->get_data(),
            array (
                'integer' => 42,
                'string' => 'text'
                ),
            "Loaded configuration is incorrect");
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid configuration file vfs://baseDirectory/wrs_config.php
     */
    public function testConfigLoadDefaultOptionalWithFileInvalidContent() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $this->setup_config_content(self::SAMPLE_INPUT_NOT_AN_ARRAY);
        $config->load_default_optional();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Configuration file vfs://baseDirectory/non_existing_file.php not found
     */
    public function testConfigLoadCustomRequiredWithoutFile() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $config->load_custom_required(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . "non_existing_file.php"));
    }

    public function testConfigLoadCustomRequiredWithFile() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $this->setup_config_content(self::SAMPLE_INPUT_VALID);
        $config->load_custom_required(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . Config::CONFIG_FILE));
        $this->assertEquals(
            $config->get_data(),
            array (
                'integer' => 42,
                'string' => 'text'
                ),
            "Loaded configuration is incorrect");
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid configuration file vfs://baseDirectory/wrs_config.php
     */
    public function testConfigLoadCustomRequiredWithFileInvalidContent() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $this->setup_config_content(self::SAMPLE_INPUT_NOT_AN_ARRAY);
        $config->load_custom_required(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . Config::CONFIG_FILE));
    }

    public function testConfigSetInt() {
        $key = "integer";
        $value = 42;
        $config = new Config(vfsStream::url('baseDirectory'));
        $config->set_int($key, $value);
        $this->assertEquals(
            $config->get($key),
            $value,
            "invalid config value");
    }

    public function testConfigSetText() {
        $key = "string";
        $value = "text";
        $config = new Config(vfsStream::url('baseDirectory'));
        $config->set_string($key, $value);
        $this->assertEquals(
            $config->get($key),
            $value,
            "invalid config value");
    }

    public function testConfigGetInexistant() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $value = $config->get("inexistant");
        $this->assertNull($value,
            "Non-existing configuration should be NULL");
    }

    public function testConfigSave() {
        $config = new Config(vfsStream::url('baseDirectory'));
        $config->set_int("integer", 42);
        $config->set_string("string", "text");
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(Config::CONFIG_FILE),
            "config file already present");

        $config->save();
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(Config::CONFIG_FILE),
            "config file is absent");

        $content = file_get_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . Config::CONFIG_FILE));
        $this->assertNotSame(FALSE, $content, "content is FALSE");
        $this->assertEquals(self::SAMPLE_INPUT_VALID, $content, "output differ");
    }
}
