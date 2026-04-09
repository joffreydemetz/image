<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Copyright;

class CopyrightTest extends ImageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->mkdir($this->tempDir . '/protect');
    }

    public function testConstructorDefaults(): void
    {
        $copyright = new Copyright($this->tempDir);

        $this->assertEquals($this->tempDir, $copyright->basePath);
        $this->assertEquals('protect', $copyright->originalsFolder);
        $this->assertEquals('nepascopier.png', $copyright->watermarkFile);
        $this->assertEquals('repeat', $copyright->copyrightType);
    }

    public function testProtectImageThrowsOnMissingFile(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not found');

        $copyright = new Copyright($this->tempDir);
        $copyright->protectImage('nonexistent.jpg');
    }

    public function testProtectImageThrowsOnNonImageFile(): void
    {
        file_put_contents($this->tempDir . '/text.txt', 'not an image');

        $this->expectException(\Exception::class);

        $copyright = new Copyright($this->tempDir);
        $copyright->protectImage('text.txt');
    }

    public function testProtectAndUnprotectImage(): void
    {
        $this->createJpeg('photo.jpg', 200, 100);
        $this->createPng('watermark.png', 50, 50);

        $originalContent = file_get_contents($this->tempDir . '/photo.jpg');

        $copyright = new Copyright($this->tempDir, 'protect', 'watermark.png');
        $copyright->protectImage('photo.jpg');

        // Original should be backed up
        $this->assertFileExists($this->tempDir . '/protect/photo.jpg');

        // Watermarked file should differ from original
        $watermarkedContent = file_get_contents($this->tempDir . '/photo.jpg');
        $this->assertNotEquals($originalContent, $watermarkedContent);

        // Unprotect restores original
        $copyright->unprotectImage('photo.jpg');

        $restoredContent = file_get_contents($this->tempDir . '/photo.jpg');
        $this->assertEquals($originalContent, $restoredContent);

        // Backup should be removed
        $this->assertFileDoesNotExist($this->tempDir . '/protect/photo.jpg');
    }

    public function testUnprotectThrowsWhenNoBackupExists(): void
    {
        $this->createJpeg('photo.jpg', 200, 100);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Original file not found');

        $copyright = new Copyright($this->tempDir);
        $copyright->unprotectImage('photo.jpg');
    }

    public function testProtectDoesNotOverwriteExistingBackup(): void
    {
        $this->createJpeg('photo.jpg', 200, 100);
        $this->createPng('watermark.png', 50, 50);

        // Create a real image as backup manually
        $this->createJpeg('protect/photo.jpg', 50, 50);
        $backupContent = file_get_contents($this->tempDir . '/protect/photo.jpg');

        $copyright = new Copyright($this->tempDir, 'protect', 'watermark.png');
        $copyright->protectImage('photo.jpg');

        // The manual backup should still be the same (not overwritten)
        $this->assertEquals($backupContent, file_get_contents($this->tempDir . '/protect/photo.jpg'));
    }
}
