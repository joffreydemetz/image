<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Copyright;
use PHPUnit\Framework\Attributes\DataProvider;

class CopyrightTest extends ImageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->mkdir($this->tempDir . '/protect');
    }

    public static function failureProvider(): array
    {
        return [
            'protect a missing source' => [
                static fn (self $test) => (new Copyright($test->tempDir))->protectImage('nonexistent.jpg'),
                'Source file "nonexistent.jpg" not found !',
            ],
            'protect a non-image source' => [
                static function (self $test) {
                    file_put_contents($test->tempDir . '/text.txt', 'not an image');
                    (new Copyright($test->tempDir))->protectImage('text.txt');
                },
                'Source file "text.txt" has an invalid mime type !',
            ],
            'protect an image with an unlisted extension' => [
                static function (self $test) {
                    $test->createJpeg('photo.bmp');
                    (new Copyright($test->tempDir))->protectImage('photo.bmp');
                },
                'Source file is not an image !',
            ],
            'protect with a missing watermark' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg');
                    (new Copyright($test->tempDir))->protectImage('photo.jpg');
                },
                'Source file "nepascopier.png" not found !',
            ],
            'protect with a non-png watermark' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg');
                    $test->createJpeg('mark.jpg', 30, 30);
                    (new Copyright($test->tempDir, 'protect', 'mark.jpg'))->protectImage('photo.jpg');
                },
                'Source file is not an image !',
            ],
            'protect with a corrupt watermark' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg');
                    $test->createSolidPng('mark.png', 30, 30, 255, 0, 0);
                    // keep the PNG signature and header, drop the pixel data
                    $test->fs->dumpFile($test->tempDir . '/bad.png', substr(file_get_contents($test->tempDir . '/mark.png'), 0, 40));
                    (new Copyright($test->tempDir, 'protect', 'bad.png'))->protectImage('photo.jpg');
                },
                "Error watermarking the source\nUnable to open image {base}/bad.png",
            ],
            'unprotect a missing source' => [
                static fn (self $test) => (new Copyright($test->tempDir))->unprotectImage('nonexistent.jpg'),
                'Source file "nonexistent.jpg" not found !',
            ],
            'unprotect without a backup' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg');
                    (new Copyright($test->tempDir))->unprotectImage('photo.jpg');
                },
                'File cannot be unprotected .. Original file not found',
            ],
            'unprotect from an unreadable backup' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg');
                    // a folder where the backup file should be
                    $test->fs->mkdir($test->tempDir . '/protect/photo.jpg');
                    (new Copyright($test->tempDir))->unprotectImage('photo.jpg');
                },
                "Error reverting to the original version of the source\nFailed to copy \"{base}/protect/photo.jpg\" because file does not exist.",
            ],
        ];
    }

    #[DataProvider('failureProvider')]
    public function testFailureThrowsWithExactMessage(\Closure $act, string $message): void
    {
        $this->expectException(\Exception::class);
        $this->expectExactExceptionMessage(str_replace('{base}', $this->tempDir, $message));

        $act($this);
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

    public function testRepeatModeTilesTheWatermark(): void
    {
        $this->createSolidJpeg('photo.jpg', 200, 100, 0, 0, 255);
        $this->createSolidPng('mark.png', 30, 30, 255, 0, 0);

        (new Copyright($this->tempDir, 'protect', 'mark.png'))->protectImage('photo.jpg');

        // a tile every 30 + 10 px on both axes, from 0,0
        [$red, , $blue] = $this->jpegPixel('photo.jpg', 5, 5);
        $this->assertGreaterThan(200, $red);
        $this->assertLessThan(60, $blue);

        [$red, , $blue] = $this->jpegPixel('photo.jpg', 45, 45);
        $this->assertGreaterThan(200, $red);
        $this->assertLessThan(60, $blue);

        // the 10 px gap between two tiles keeps the source
        [$red, , $blue] = $this->jpegPixel('photo.jpg', 35, 5);
        $this->assertLessThan(60, $red);
        $this->assertGreaterThan(200, $blue);
    }

    public function testNonRepeatModeBacksUpButPastesNoWatermark(): void
    {
        $this->createSolidJpeg('photo.jpg', 200, 100, 0, 0, 255);
        $this->createSolidPng('mark.png', 30, 30, 255, 0, 0);
        $originalContent = file_get_contents($this->tempDir . '/photo.jpg');

        $copyright = new Copyright($this->tempDir, 'protect', 'mark.png', 'center');
        $copyright->protectImage('photo.jpg');

        // the original is kept aside ...
        $this->assertSame($originalContent, file_get_contents($this->tempDir . '/protect/photo.jpg'));

        // ... but only 'repeat' pastes anything: the pixel under the first tile is untouched
        [$red, , $blue] = $this->jpegPixel('photo.jpg', 5, 5);
        $this->assertLessThan(60, $red);
        $this->assertGreaterThan(200, $blue);

        $copyright->unprotectImage('photo.jpg');
        $this->assertSame($originalContent, file_get_contents($this->tempDir . '/photo.jpg'));
    }

    public function testProtectAcceptsAnUppercaseExtension(): void
    {
        $this->createJpeg('photo.JPG', 200, 100);
        $this->createPng('watermark.png', 50, 50);
        $originalContent = file_get_contents($this->tempDir . '/photo.JPG');

        (new Copyright($this->tempDir, 'protect', 'watermark.png'))->protectImage('photo.JPG');

        $this->assertSame($originalContent, file_get_contents($this->tempDir . '/protect/photo.JPG'));
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
