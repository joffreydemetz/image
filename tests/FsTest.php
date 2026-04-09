<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Fs;

class FsTest extends ImageTestCase
{
    public function testConstructorDefaults(): void
    {
        $fs = new Fs($this->tempDir);

        $this->assertEquals($this->tempDir, $fs->basePath);
        $this->assertEquals('media', $fs->mediasFolder);
        $this->assertEquals('thumbs', $fs->thumbsFolder);
        $this->assertEquals('protect', $fs->copyrightsFolder);
    }

    public function testConstructorCustomFolders(): void
    {
        $fs = new Fs($this->tempDir, 'images', 'cache', 'originals');

        $this->assertEquals('images', $fs->mediasFolder);
        $this->assertEquals('cache', $fs->thumbsFolder);
        $this->assertEquals('originals', $fs->copyrightsFolder);
    }

    public function testCheckCreatesFolders(): void
    {
        $fs = new Fs($this->tempDir);

        ob_start();
        $fs->check();
        ob_end_clean();

        $this->assertDirectoryExists($this->tempDir . '/media');
        $this->assertDirectoryExists($this->tempDir . '/thumbs');
        $this->assertDirectoryExists($this->tempDir . '/protect');
    }

    public function testCheckWithCustomFolders(): void
    {
        $fs = new Fs($this->tempDir, 'imgs', 'th', 'orig');

        ob_start();
        $fs->check();
        ob_end_clean();

        $this->assertDirectoryExists($this->tempDir . '/imgs');
        $this->assertDirectoryExists($this->tempDir . '/th');
        $this->assertDirectoryExists($this->tempDir . '/orig');
    }

    public function testCheckDoesNotRecreateExistingFolders(): void
    {
        $this->fs->mkdir($this->tempDir . '/media');
        $this->fs->mkdir($this->tempDir . '/thumbs');
        $this->fs->mkdir($this->tempDir . '/protect');

        $fs = new Fs($this->tempDir);

        ob_start();
        $fs->check();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function testCheckThrowsOnEmptyBasePath(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('basePath cannot be empty');

        $fs = new Fs('');
        $fs->check();
    }

    public function testCheckThrowsOnInvalidBasePath(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('does not exist');

        $fs = new Fs('/nonexistent/path/xyz');
        $fs->check();
    }
}
