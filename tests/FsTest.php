<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Fs;
use PHPUnit\Framework\Attributes\DataProvider;

class FsTest extends ImageTestCase
{
    public function testCheckCreatesFolders(): void
    {
        $fs = new Fs($this->tempDir);

        ob_start();
        $fs->check();
        $output = ob_get_clean();

        $this->assertDirectoryExists($this->tempDir . '/media');
        $this->assertDirectoryExists($this->tempDir . '/thumbs');
        $this->assertDirectoryExists($this->tempDir . '/protect');
        $this->assertSame("create folder /media/\ncreate folder /thumbs/\ncreate folder /protect/\n", $output);
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

    public static function invalidBasePathProvider(): array
    {
        return [
            'empty' => ['', 'basePath cannot be empty'],
            'missing folder' => ['/nonexistent/path/xyz', 'basePath "/nonexistent/path/xyz" does not exist'],
        ];
    }

    #[DataProvider('invalidBasePathProvider')]
    public function testCheckThrowsOnInvalidBasePath(string $basePath, string $message): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExactExceptionMessage($message);

        (new Fs($basePath))->check();
    }
}
