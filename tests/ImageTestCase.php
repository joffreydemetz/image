<?php

namespace JDZ\Image\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class ImageTestCase extends TestCase
{
    protected string $tempDir;
    protected Filesystem $fs;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . '/jdz-image-' . uniqid();
        $this->fs->mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            // some tests lock files or folders to force IO failures
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tempDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($items as $item) {
                chmod($item->getPathname(), $item->isDir() ? 0755 : 0644);
            }

            $this->fs->remove($this->tempDir);
        }
    }

    protected function expectExactExceptionMessage(string $message): void
    {
        $this->expectExceptionMessageMatches('/\A' . preg_quote($message, '/') . '\z/');
    }

    protected function createJpeg(string $relPath, int $width = 200, int $height = 100): string
    {
        $fullPath = $this->tempDir . '/' . $relPath;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            $this->fs->mkdir($dir);
        }

        $img = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
        imagefill($img, 0, 0, $color);
        imagejpeg($img, $fullPath, 90);
        imagedestroy($img);

        return $relPath;
    }

    protected function createSolidJpeg(string $relPath, int $width, int $height, int $red, int $green, int $blue): string
    {
        $img = imagecreatetruecolor($width, $height);
        imagefill($img, 0, 0, imagecolorallocate($img, $red, $green, $blue));
        imagejpeg($img, $this->tempDir . '/' . $relPath, 90);
        imagedestroy($img);

        return $relPath;
    }

    protected function createSolidPng(string $relPath, int $width, int $height, int $red, int $green, int $blue): string
    {
        $img = imagecreatetruecolor($width, $height);
        imagefill($img, 0, 0, imagecolorallocate($img, $red, $green, $blue));
        imagepng($img, $this->tempDir . '/' . $relPath);
        imagedestroy($img);

        return $relPath;
    }

    protected function createPng(string $relPath, int $width = 200, int $height = 100): string
    {
        $fullPath = $this->tempDir . '/' . $relPath;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            $this->fs->mkdir($dir);
        }

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        imagepng($img, $fullPath);
        imagedestroy($img);

        return $relPath;
    }

    protected function createGif(string $relPath, int $width = 200, int $height = 100): string
    {
        $fullPath = $this->tempDir . '/' . $relPath;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            $this->fs->mkdir($dir);
        }

        $img = imagecreatetruecolor($width, $height);
        imagegif($img, $fullPath);
        imagedestroy($img);

        return $relPath;
    }

    /**
     * Returns [red, green, blue] of one pixel of a JPEG under tempDir.
     */
    protected function jpegPixel(string $relPath, int $x, int $y): array
    {
        $img = imagecreatefromjpeg($this->tempDir . '/' . $relPath);
        $rgb = imagecolorsforindex($img, imagecolorat($img, $x, $y));
        imagedestroy($img);

        return [$rgb['red'], $rgb['green'], $rgb['blue']];
    }
}
