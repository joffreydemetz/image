<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Image;

class ImageTest extends ImageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->mkdir($this->tempDir . '/thumbs');
    }

    public function testLoadValidImage(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->load('photo.jpg');

        $this->assertTrue($image->valid);
        $this->assertTrue($image->exists);
        $this->assertSame('photo.jpg', $image->source->srcFile);
        $this->assertNull($image->thumb);
    }

    public function testLoadInvalidImageWithDefault(): void
    {
        $this->createJpeg('default.jpg', 100, 100);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->lazy = true;
        $image->load('missing.jpg', 'default.jpg');

        $this->assertTrue($image->valid);
        $this->assertFalse($image->exists);
        $this->assertFalse($image->lazy, 'a default image is never lazy-loaded');
        $this->assertSame('default.jpg', $image->source->srcFile);
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
        $this->assertSame('thumbs/_photo-800.jpg', $image->thumb);
        $this->assertFileExists($this->tempDir . '/thumbs/_photo-800.jpg');
    }

    public function testLoadWithLazyReusesAnExistingThumb(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);
        // whatever sits at the thumb path is taken as is, never rebuilt
        $this->createJpeg('thumbs/_photo-800.jpg', 10, 10);
        $existing = file_get_contents($this->tempDir . '/thumbs/_photo-800.jpg');

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->lazy = true;
        $image->load('photo.jpg');

        $this->assertSame('thumbs/_photo-800.jpg', $image->thumb);
        $this->assertSame($existing, file_get_contents($this->tempDir . '/thumbs/_photo-800.jpg'));
    }

    public function testLoadWithLazySmallImageNoThumb(): void
    {
        $this->createJpeg('small.jpg', 400, 300);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->lazy = true;
        $image->targetWidth = 800;
        $image->load('small.jpg');

        $this->assertTrue($image->valid);
        $this->assertNull($image->thumb);
    }

    public function testGetPicWithLazyThumbUsesTheThumbAsSrc(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->lazy = true;
        $pic = $image->load('photo.jpg')->getPic('Alt');

        $this->assertSame('https://cdn.com/thumbs/_photo-800.jpg', $pic->attrs['src']);
        $this->assertSame('https://cdn.com/photo.jpg', $pic->dataAttrs['src']);
    }

    public function testGetPicWithLazySmallImageUsesTheSourceAsSrc(): void
    {
        $this->createJpeg('small.jpg', 400, 300);

        $image = new Image($this->tempDir, 'https://cdn.com/');
        $image->lazy = true;
        $pic = $image->load('small.jpg')->getPic('Alt');

        $this->assertSame('https://cdn.com/small.jpg', $pic->attrs['src']);
        $this->assertArrayNotHasKey('src', $pic->dataAttrs);
    }

    public function testGetPicThrowsOnInvalidSource(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExactExceptionMessage('Cannot export an invalid image ..');

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
