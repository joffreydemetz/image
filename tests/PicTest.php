<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Img;
use JDZ\Image\Pic;

class PicTest extends ImageTestCase
{
    public function testLoadSetsAttrs(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'Alt text', 'https://cdn.com/');
        $pic->load();

        $this->assertEquals('https://cdn.com/photo.jpg', $pic->attrs['src']);
        $this->assertEquals('Alt text', $pic->attrs['alt']);
        $this->assertIsArray($pic->attrs['class']);
        $this->assertEquals('landscape', $pic->dataAttrs['orientation']);
    }

    public function testLoadWithThumbUsesThumbAsSrc(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'Alt', 'https://cdn.com/');
        $pic->thumb = 'thumbs/photo-400.jpg';
        $pic->load();

        $this->assertEquals('https://cdn.com/thumbs/photo-400.jpg', $pic->attrs['src']);
        $this->assertEquals('https://cdn.com/photo.jpg', $pic->dataAttrs['src']);
    }

    public function testLoadWithForceSizeAddsWidthHeight(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'Alt');
        $pic->forceSize = true;
        $pic->load();

        $this->assertEquals(800, $pic->attrs['width']);
        $this->assertEquals(600, $pic->attrs['height']);
    }

    public function testLoadWithoutForceSizeOmitsWidthHeight(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'Alt');
        $pic->load();

        $this->assertArrayNotHasKey('width', $pic->attrs);
        $this->assertArrayNotHasKey('height', $pic->attrs);
    }

    public function testToAttributesRendersScalarValues(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, '');
        $pic->forceSize = true;
        $pic->load();
        $pic->attrs['title'] = '  padded  ';
        $pic->attrs['draggable'] = false;
        $pic->dataAttrs['zoom'] = true;

        $this->assertSame([
            'src' => 'photo.jpg',
            'alt' => '',
            'width' => '800',
            'height' => '600',
            'title' => 'padded',
            'draggable' => 'false',
            'data-orientation' => 'landscape',
            'data-zoom' => 'true',
        ], $pic->toAttributes());
    }

    public function testToStringRendersImgTag(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'My photo', 'https://cdn.com/');
        $pic->load();

        $html = $pic->toString();

        $this->assertStringStartsWith('<img ', $html);
        $this->assertStringEndsWith(' />', $html);
        $this->assertStringContainsString('src="https://cdn.com/photo.jpg"', $html);
        $this->assertStringContainsString('alt="My photo"', $html);
        $this->assertStringContainsString('data-orientation="landscape"', $html);
    }

    public function testMagicToString(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'Alt');
        $pic->load();

        $this->assertEquals($pic->toString(), (string)$pic);
    }

    public function testSquareImageOmitsOrientation(): void
    {
        $this->createJpeg('square.jpg', 300, 300);
        $img = new Img($this->tempDir, 'square.jpg');

        $pic = new Pic($img, 'Square');
        $pic->load();

        $this->assertArrayNotHasKey('orientation', $pic->dataAttrs);
    }

    public function testEmptyClassIsOmittedFromAttributes(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'Alt');
        $pic->load();

        $attrs = $pic->toAttributes();

        $this->assertArrayNotHasKey('class', $attrs);
    }

    public function testClassArrayJoinedToString(): void
    {
        $this->createJpeg('photo.jpg', 800, 600);
        $img = new Img($this->tempDir, 'photo.jpg');

        $pic = new Pic($img, 'Alt');
        $pic->load();
        $pic->attrs['class'][] = 'responsive';
        $pic->attrs['class'][] = 'gallery';

        $attrs = $pic->toAttributes();

        $this->assertEquals('responsive gallery', $attrs['class']);
    }
}
