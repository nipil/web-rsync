<?php

declare(strict_types=1);

namespace WRS\Tests\Storage;

use PHPUnit\Framework\TestCase;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

use WRS\Storage\FileStorage;

class FileStorageTest extends TestCase
{
    const DIR = "baseDirectory";
    const ABS = "abs";
    const FILE = "file";
    const CONTENT = "content";

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory(self::DIR));
    }

    public function testCreateDirectoryIfNotExistsSuccessCreate()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::ABS));
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "Directory present");
        $fs->createDirectoryIfNotExists();
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "Directory absent");
    }

    public function testCreateDirectoryIfNotExistsSuccessExists()
    {
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "Directory present");
        mkdir(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::ABS));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "Directory absent");
        $fs = new FileStorage(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::ABS));
        $this->assertSame(false, $fs->createDirectoryIfNotExists(), "Return value");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Path is not a directory : .*#
     */
    public function testCreateDirectoryIfNotExistsFailExistNotDirectory()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR));
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "File present");
        $fs->save(self::ABS, self::CONTENT);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "File absent");
        $fs = new FileStorage(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::ABS));
        $this->assertSame(false, $fs->createDirectoryIfNotExists(), "Return value");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Could not create directory : .*#
     */
    public function testCreateDirectoryIfNotExistsFailCreateFailed()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::ABS.DIRECTORY_SEPARATOR.self::ABS));
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "Directory present");
        mkdir(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::ABS));
        chmod(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::ABS), 0000);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "Directory absent");
        $this->assertSame(false, $fs->createDirectoryIfNotExists(), "Return value");
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

    public function testExists()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR));
        file_put_contents(vfsStream::url(self::DIR.DIRECTORY_SEPARATOR.self::FILE), self::CONTENT);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(self::FILE), "File should be present");
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild(self::ABS), "File should be absent");
        $this->assertTrue($fs->exists(self::FILE), "Absent but should be present");
        $this->assertFalse($fs->exists(self::ABS), "Present but should be absent");
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot save data to file vfs://baseDirectory/abs/file
     */
    public function testSaveFail()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR . DIRECTORY_SEPARATOR . self::ABS));
        $fs->save(self::FILE, self::CONTENT);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot get content of file vfs://baseDirectory/abs/file
     */
    public function testLoadFail()
    {
        $fs = new FileStorage(vfsStream::url(self::DIR . DIRECTORY_SEPARATOR . self::ABS));
        $fs->load(self::FILE);
    }
}
