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
        $this->tempDir = sys_get_temp_dir() . '/jdz_image_test_' . uniqid();
        $this->fs->mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->fs->remove($this->tempDir);
        }
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
}
