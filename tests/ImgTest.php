<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Img;

class ImgTest extends ImageTestCase
{
    public function testValidJpegImage(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);

        $img = new Img($this->tempDir, 'photo.jpg');

        $this->assertTrue($img->valid);
        $this->assertEquals(800, $img->width);
        $this->assertEquals(600, $img->height);
        $this->assertEquals('landscape', $img->orientation);
        $this->assertEquals(IMAGETYPE_JPEG, $img->type);
    }

    public function testValidPngImage(): void
    {
        $this->createPng('icon.png', 100, 100);

        $img = new Img($this->tempDir, 'icon.png');

        $this->assertTrue($img->valid);
        $this->assertEquals(100, $img->width);
        $this->assertEquals(100, $img->height);
        $this->assertEquals('', $img->orientation);
        $this->assertEquals(IMAGETYPE_PNG, $img->type);
    }

    public function testPortraitOrientation(): void
    {
        $this->createJpeg('portrait.jpg', 400, 600);

        $img = new Img($this->tempDir, 'portrait.jpg');

        $this->assertEquals('portrait', $img->orientation);
    }

    public function testSquareHasNoOrientation(): void
    {
        $this->createJpeg('square.jpg', 300, 300);

        $img = new Img($this->tempDir, 'square.jpg');

        $this->assertEquals('', $img->orientation);
    }

    public function testInvalidFileIsNotValid(): void
    {
        $img = new Img($this->tempDir, 'nonexistent.jpg');

        $this->assertFalse($img->valid);
    }

    public function testEmptySrcFileIsNotValid(): void
    {
        $img = new Img($this->tempDir, '');

        $this->assertFalse($img->valid);
    }

    public function testNonImageFileIsNotValid(): void
    {
        file_put_contents($this->tempDir . '/text.txt', 'not an image');

        $img = new Img($this->tempDir, 'text.txt');

        $this->assertFalse($img->valid);
    }

    public function testGetFilePath(): void
    {
        $this->createJpeg('photo.jpg');

        $img = new Img($this->tempDir, 'photo.jpg');

        $this->assertEquals($this->tempDir . '/photo.jpg', $img->getFilePath());
    }

    public function testFileInfoExtraction(): void
    {
        $this->createJpeg('media/photos/vacation.jpg');

        $img = new Img($this->tempDir, 'media/photos/vacation.jpg');

        $this->assertEquals('media/photos', $img->path);
        $this->assertEquals('vacation', $img->name);
        $this->assertEquals('jpg', $img->ext);
    }

    public function testGetThumbBaseName(): void
    {
        $this->createJpeg('media/photos/pic.jpg');

        $img = new Img($this->tempDir, 'media/photos/pic.jpg');

        $this->assertEquals('media_photos_pic', $img->getThumbBaseName());
    }

    public function testGetThumbName(): void
    {
        $this->createJpeg('media/photos/pic.jpg');

        $img = new Img($this->tempDir, 'media/photos/pic.jpg');

        $this->assertEquals('media_photos_pic-800.jpg', $img->getThumbName(800));
        $this->assertEquals('media_photos_pic-400.jpg', $img->getThumbName(400));
    }

    public function testGifImage(): void
    {
        $this->createGif('anim.gif', 320, 240);

        $img = new Img($this->tempDir, 'anim.gif');

        $this->assertTrue($img->valid);
        $this->assertEquals(IMAGETYPE_GIF, $img->type);
        $this->assertEquals(320, $img->width);
        $this->assertEquals(240, $img->height);
    }
}
