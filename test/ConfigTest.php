<?php

declare(strict_types=1);

namespace WRS;

use PHPUnit\Framework\TestCase;

use org\bovigo\vfs\vfsStream, org\bovigo\vfs\vfsStreamWrapper, org\bovigo\vfs\vfsStreamDirectory;

class ConfigTest extends TestCase
{
    const SAMPLE_INPUT_VALID = "<?php" . PHP_EOL
        . "return array (" . PHP_EOL
        . "  'example' => 'test'," . PHP_EOL
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
            array ('example' => 'test'),
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
            array ('example' => 'test'),
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
}
