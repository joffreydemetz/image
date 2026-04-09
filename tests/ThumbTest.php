<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Thumb;

class ThumbTest extends ImageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->mkdir($this->tempDir . '/thumbs');
    }

    public function testConstructorDefaults(): void
    {
        $thumb = new Thumb($this->tempDir);

        $this->assertEquals($this->tempDir, $thumb->basePath);
        $this->assertEquals(800, $thumb->targetWidth);
        $this->assertEquals('thumbs', $thumb->thumbsFolder);
        $this->assertEquals(0, $thumb->cacheLife);
        $this->assertNull($thumb->thumbFile);
        $this->assertFalse($thumb->thumbed);
    }

    public function testThumbImageCreatesJpegThumbnail(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);

        $thumb = new Thumb($this->tempDir, 800);
        $result = $thumb->thumbImage('photo.jpg');

        $this->assertTrue($result);
        $this->assertTrue($thumb->thumbed);
        $this->assertNotNull($thumb->thumbFile);
        $this->assertFileExists($this->tempDir . '/' . $thumb->thumbFile);
    }

    public function testThumbImageCreatesPngThumbnail(): void
    {
        $this->createPng('icon.png', 2000, 1000);

        $thumb = new Thumb($this->tempDir, 500);
        $result = $thumb->thumbImage('icon.png');

        $this->assertTrue($result);
        $this->assertTrue($thumb->thumbed);
        $this->assertFileExists($this->tempDir . '/' . $thumb->thumbFile);
    }

    public function testThumbImageCreatesGifThumbnail(): void
    {
        $this->createGif('anim.gif', 1200, 800);

        $thumb = new Thumb($this->tempDir, 600);
        $result = $thumb->thumbImage('anim.gif');

        $this->assertTrue($result);
        $this->assertFileExists($this->tempDir . '/' . $thumb->thumbFile);
    }

    public function testSmallImageSkipsThumbnailing(): void
    {
        $this->createJpeg('small.jpg', 400, 300);

        $thumb = new Thumb($this->tempDir, 800);
        $result = $thumb->thumbImage('small.jpg');

        $this->assertFalse($result);
        $this->assertFalse($thumb->thumbed);
        $this->assertEquals('small.jpg', $thumb->thumbFile);
    }

    public function testCachedThumbIsReused(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('photo.jpg');

        $thumb2 = new Thumb($this->tempDir, 800);
        $result = $thumb2->thumbImage('photo.jpg');

        $this->assertFalse($result);
        $this->assertTrue($thumb2->thumbed);
        $this->assertNotNull($thumb2->thumbFile);
    }

    public function testForceRegeneratesThumb(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('photo.jpg');

        $thumb2 = new Thumb($this->tempDir, 800);
        $result = $thumb2->thumbImage('photo.jpg', true);

        $this->assertTrue($result);
        $this->assertTrue($thumb2->thumbed);
    }

    public function testInvalidImageThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('nonexistent.jpg');
    }

    public function testUnthumbImageDeletesThumbnails(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('photo.jpg');
        $thumbFile = $this->tempDir . '/' . $thumb->thumbFile;
        $this->assertFileExists($thumbFile);

        $result = $thumb->unthumbImage('photo.jpg');

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($thumbFile);
    }

    public function testUnthumbInvalidImageThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->unthumbImage('nonexistent.jpg');
    }

    public function testThumbPreservesAspectRatio(): void
    {
        $this->createJpeg('wide.jpg', 2000, 1000);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('wide.jpg');

        $thumbPath = $this->tempDir . '/' . $thumb->thumbFile;
        list($w, $h) = getimagesize($thumbPath);

        $this->assertEquals(800, $w);
        $this->assertEquals(400, $h);
    }

    public function testThumbInSubdirectory(): void
    {
        $this->createJpeg('media/photos/pic.jpg', 1600, 1200);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('media/photos/pic.jpg');

        $this->assertTrue($thumb->thumbed);
        $this->assertStringContainsString('media_photos_pic-800', $thumb->thumbFile);
    }
}
