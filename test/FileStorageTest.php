<?php

declare(strict_types=1);

namespace WRS\Tests;

use PHPUnit\Framework\TestCase;

use org\bovigo\vfs\vfsStream,
    org\bovigo\vfs\vfsStreamWrapper,
    org\bovigo\vfs\vfsStreamDirectory;

use WRS\Storage\FileStorage;

class FileStorageTest extends TestCase
{
    const DIR = "baseDirectory";
    const ABS = self::DIR . DIRECTORY_SEPARATOR . "abs";
    const FILE = "file";
    const CONTENT = "content";

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory(self::DIR));
    }

    public function testSaveSuccess()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR));
        $fs->save(self::FILE, self::CONTENT);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(self::FILE), "File absent");
        $this->assertSame(self::CONTENT, file_get_contents(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::FILE)));
    }

    public function testLoadSuccess()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR));
        file_put_contents(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::FILE), self::CONTENT);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(self::FILE), "File absent");
        $content = $fs->load(self::FILE);
        $this->assertSame(self::CONTENT, $content);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot save data to file vfs://baseDirectory/abs/file
     */
    public function testSaveFail()
    {
        $fs = new FileStorage(vfsStream::url(self::ABS));
        $fs->save(self::FILE, self::CONTENT);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot get content of file vfs://baseDirectory/abs/file
     */
    public function testLoadFail()
    {
        $fs = new FileStorage(vfsStream::url(self::ABS));
        $fs->load(self::FILE);
    }
}
