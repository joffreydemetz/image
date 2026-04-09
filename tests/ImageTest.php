<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Image;
use JDZ\Image\Pic;

class ImageTest extends ImageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->mkdir($this->tempDir . '/thumbs');
    }

    public function testConstructor(): void
    {
        $image = new Image($this->tempDir, 'https://cdn.com/');

        $this->assertEquals($this->tempDir, $image->basePath);
        $this->assertEquals('https://cdn.com/', $image->baseUrl);
        $this->assertEquals('thumbs', $image->thumbsFolder);
        $this->assertFalse($image->lazy);
        $this->assertFalse($image->exists);
        $this->assertFalse($image->valid);
    }

    public function testLoadValidImage(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $result = $image->load('photo.jpg');

        $this->assertTrue($image->valid);
        $this->assertTrue($image->exists);
        $this->assertNotNull($image->source);
        $this->assertSame($image, $result);
    }

    public function testLoadInvalidImageWithDefault(): void
    {
        $this->createJpeg('default.jpg', 100, 100);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->load('missing.jpg', 'default.jpg');

        $this->assertTrue($image->valid);
        $this->assertFalse($image->exists);
    }

    public function testLoadInvalidImageNoDefault(): void
    {
        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->load('missing.jpg');

        $this->assertFalse($image->valid);
        $this->assertFalse($image->exists);
    }

    public function testLoadWithLazyCreatesThumb(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->lazy = true;
        $image->targetWidth = 800;
        $image->load('photo.jpg');

        $this->assertTrue($image->valid);
        $this->assertNotNull($image->thumb);
    }

    public function testLoadWithLazySmallImageNoThumb(): void
    {
        $this->createJpeg('small.jpg', 400, 300);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->lazy = true;
        $image->targetWidth = 800;
        $image->load('small.jpg');

        $this->assertTrue($image->valid);
    }

    public function testGetPicReturnsValidPic(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->load('photo.jpg');

        $pic = $image->getPic('My photo');

        $this->assertInstanceOf(Pic::class, $pic);
        $this->assertEquals('My photo', $pic->alt);
        $this->assertEquals('https://cdn.com/', $pic->baseUrl);
    }

    public function testGetPicThrowsOnInvalidSource(): void
    {
        $this->expectException(\Exception::class);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->load('missing.jpg');
        $image->getPic('Alt');
    }

    public function testGetPicAddsDefaultClassWhenImageMissing(): void
    {
        $this->createJpeg('default.jpg', 100, 100);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->load('missing.jpg', 'default.jpg');

        $pic = $image->getPic('Alt');

        $this->assertContains('default', $pic->attrs['class']);
    }

    public function testGetPicWithStyle(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->load('photo.jpg');

        $pic = $image->getPic('Alt', 'max-width: 100%');

        $this->assertEquals('max-width: 100%', $pic->attrs['style']);
    }
}
