<?php

declare(strict_types=1);

namespace WRS\Tests\Storage;

use PHPUnit\Framework\TestCase;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

use WRS\Storage\FileStorage;

class FileStorageTest extends TestCase
{
    const DIR = "rootdir";

    private $vfsroot;

    public static function setUpBeforeClass()
    {
        vfsStreamWrapper::register();
    }

    public function setUp()
    {
        $this->vfsroot = vfsStream::setup(self::DIR);
    }

    protected function vfs(... $segments)
    {
        array_unshift($segments, self::DIR);
        return vfsStream::url(join(DIRECTORY_SEPARATOR, $segments));
    }

    public function testCreateDirectoryIfNotExistsSuccessCreate()
    {
        $this->assertFalse($this->vfsroot->hasChild("abs"), "Directory should be absent");

        $fs = new FileStorage($this->vfs("abs"));
        $fs->createDirectoryIfNotExists();

        $this->assertTrue($this->vfsroot->hasChild("abs"), "Directory should be present");
    }

    public function testCreateDirectoryIfNotExistsSuccessExists()
    {
        vfsStream::create(["dir"=>[]]);
        $this->assertTrue($this->vfsroot->hasChild("dir"), "Directory should be present");

        $fs = new FileStorage($this->vfs("dir"));
        $this->assertSame(false, $fs->createDirectoryIfNotExists(), "Incorrect return value");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Path is not a directory : .*$#
     */
    public function testCreateDirectoryIfNotExistsFailExistNotDirectory()
    {
        vfsStream::create(["is_a_file"=>"file_content"]);
        $this->assertTrue($this->vfsroot->hasChild("is_a_file"), "File should be present");

        $fs = new FileStorage($this->vfs("is_a_file"));
        $fs->createDirectoryIfNotExists();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Could not create directory : .*$#
     */
    public function testCreateDirectoryIfNotExistsFailCreateFailed()
    {
        $this->vfsroot->chmod(0000);
        $this->vfsroot->chown(vfsStream::OWNER_ROOT);
        $this->vfsroot->chgrp(vfsStream::GROUP_ROOT);
        $this->assertFalse($this->vfsroot->hasChild("forbidden"), "Directory should be absent");

        $fs = new FileStorage($this->vfs("forbidden"));
        $fs->createDirectoryIfNotExists();
    }

    public function testSaveSuccess()
    {
        $this->assertFalse($this->vfsroot->hasChild("saved_file"), "File should be absent");

        $fs = new FileStorage($this->vfs());
        $fs->save("saved_file", "content");

        $this->assertSame(
            [self::DIR => ["saved_file" => "content"]],
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );
    }

    public function testLoadSuccess()
    {
        vfsStream::create(["file_to_load"=>"content"]);
        $this->assertTrue($this->vfsroot->hasChild("file_to_load"), "File should be present");

        $fs = new FileStorage($this->vfs());
        $this->assertSame("content", $fs->load("file_to_load"));
    }

    public function testExists()
    {
        vfsStream::create(["file_present"=>"content"]);
        $this->assertTrue($this->vfsroot->hasChild("file_present"), "File should be present");
        $this->assertFalse($this->vfsroot->hasChild("file_absent"), "File should be absent");

        $fs = new FileStorage($this->vfs());
        $this->assertSame(true, $fs->exists("file_present"), "Absent but should be present");
        $this->assertSame(false, $fs->exists("file_absent"), "Present but should be absent");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot save data to file .*$#
     */
    public function testSaveFailInexistantPath()
    {
        $this->assertFalse($this->vfsroot->hasChild("abs"), "Directory should be absent");

        $fs = new FileStorage($this->vfs("abs"));
        $fs->save("file", "content");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot get content of file .*$#
     */
    public function testLoadFailInexistantPath()
    {
        $this->assertFalse($this->vfsroot->hasChild("abs"), "Directory should be absent");

        $fs = new FileStorage($this->vfs("abs"));
        $fs->load("file");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot save data to file .*$#
     */
    public function testSaveFailIsADirectory()
    {
        vfsStream::create(["this_is_a_directory"=>[]]);
        $this->assertTrue($this->vfsroot->hasChild("this_is_a_directory"), "Directory should be present");

        $fs = new FileStorage($this->vfs());
        $fs->save("this_is_a_directory", "content");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Cannot get content of file .*$#
     */
    public function testLoadFailIsADirectory()
    {
        vfsStream::create(["this_is_a_directory"=>[]]);
        $this->assertTrue($this->vfsroot->hasChild("this_is_a_directory"), "Directory should be present");

        $fs = new FileStorage($this->vfs());
        $fs->load("this_is_a_directory");
    }
}
