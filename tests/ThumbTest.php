<?php

namespace JDZ\Image\Tests;

use JDZ\Image\Thumb;
use PHPUnit\Framework\Attributes\DataProvider;

class ThumbTest extends ImageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->mkdir($this->tempDir . '/thumbs');
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

    public static function cacheLifeProvider(): array
    {
        return [
            'no cacheLife never expires' => [0, 10 * 365 * 86400, false],
            'fresh thumb is reused' => [3600, 60, false],
            'stale thumb is rebuilt' => [60, 3600, true],
            'thumb as old as cacheLife is rebuilt' => [60, 60, true],
        ];
    }

    #[DataProvider('cacheLifeProvider')]
    public function testCacheLifeDecidesWhetherAnExistingThumbIsRebuilt(int $cacheLife, int $age, bool $rebuilt): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);
        (new Thumb($this->tempDir))->thumbImage('photo.jpg');

        $thumbPath = $this->tempDir . '/thumbs/_photo-800.jpg';
        $agedAt = time() - $age;
        touch($thumbPath, $agedAt);

        $thumb = new Thumb($this->tempDir, 800, 'thumbs', $cacheLife);
        $result = $thumb->thumbImage('photo.jpg');

        clearstatcache();
        $this->assertSame($rebuilt, $result);
        $this->assertTrue($thumb->thumbed);
        $this->assertSame('thumbs/_photo-800.jpg', $thumb->thumbFile);
        if ($rebuilt) {
            $this->assertGreaterThan($agedAt, filemtime($thumbPath));
        } else {
            $this->assertSame($agedAt, filemtime($thumbPath));
        }
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

    public static function failureProvider(): array
    {
        return [
            'thumb a missing source' => [
                static fn (self $test) => (new Thumb($test->tempDir))->thumbImage('nonexistent.jpg'),
                'Source file "nonexistent.jpg" is not a valid image !',
            ],
            'unthumb a missing source' => [
                static fn (self $test) => (new Thumb($test->tempDir))->unthumbImage('nonexistent.jpg'),
                'Source file "nonexistent.jpg" is not a valid image !',
            ],
            'thumb writer fails' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg', 1600, 1200);
                    $thumb = new class($test->tempDir) extends Thumb {
                        protected function doCreateThumb(string $srcFulPath, string $thumbFullPath, int $targetWidth, int $targetHeight, int $imageType)
                        {
                            throw new \RuntimeException('disk full');
                        }
                    };
                    $thumb->thumbImage('photo.jpg');
                },
                "Error creating the thumb file \ndisk full",
            ],
            'thumb writer writes nothing' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg', 1600, 1200);
                    $thumb = new class($test->tempDir) extends Thumb {
                        protected function doCreateThumb(string $srcFulPath, string $thumbFullPath, int $targetWidth, int $targetHeight, int $imageType)
                        {
                        }
                    };
                    $thumb->thumbImage('photo.jpg');
                },
                "Error creating the thumb file \nThumb file not created",
            ],
            'source type the writer does not handle' => [
                static function (self $test) {
                    imagebmp(imagecreatetruecolor(1600, 1200), $test->tempDir . '/photo.bmp');
                    (new Thumb($test->tempDir))->thumbImage('photo.bmp');
                },
                "Error creating the thumb file \nUnsupported image type image/bmp",
            ],
            'stale thumb cannot be deleted' => [
                static function (self $test) {
                    $test->createJpeg('photo.jpg', 1600, 1200);
                    (new Thumb($test->tempDir))->thumbImage('photo.jpg');
                    $test->lockFile($test->tempDir . '/thumbs/_photo-800.jpg');
                    (new Thumb($test->tempDir))->thumbImage('photo.jpg', true);
                },
                "Error deleting the thumb file \nFailed to remove file \"{base}/thumbs/_photo-800.jpg\": unlink({base}/thumbs/_photo-800.jpg): Permission denied",
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

    public function testUnthumbImageDeletesEverySizeOfItsOwnThumbs(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);
        (new Thumb($this->tempDir, 800))->thumbImage('photo.jpg');
        (new Thumb($this->tempDir, 400))->thumbImage('photo.jpg');

        $this->assertTrue((new Thumb($this->tempDir))->unthumbImage('photo.jpg'));

        $this->assertFileDoesNotExist($this->tempDir . '/thumbs/_photo-800.jpg');
        $this->assertFileDoesNotExist($this->tempDir . '/thumbs/_photo-400.jpg');
    }

    public function testUnthumbImageKeepsThumbsOfAnotherImageSharingTheNamePrefix(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);
        $this->createJpeg('photo-2.jpg', 1600, 1200);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('photo.jpg');
        $thumb->thumbImage('photo-2.jpg');

        $thumb->unthumbImage('photo.jpg');

        $this->assertFileDoesNotExist($this->tempDir . '/thumbs/_photo-800.jpg');
        $this->assertFileExists($this->tempDir . '/thumbs/_photo-2-800.jpg');
    }

    public function testUnthumbImageReportsAThumbItCannotDelete(): void
    {
        $this->createJpeg('photo.jpg', 1600, 1200);
        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('photo.jpg');
        $this->lockFile($this->tempDir . '/thumbs/_photo-800.jpg');

        $this->assertFalse($thumb->unthumbImage('photo.jpg'));
        $this->assertFileExists($this->tempDir . '/thumbs/_photo-800.jpg');
    }

    public static function targetSizeProvider(): array
    {
        return [
            'landscape wider than the target' => [2000, 1000, 800, 400],
            'square wider than the target' => [1600, 1600, 800, 800],
            'portrait wider than the target is bounded by width' => [1000, 2000, 800, 1600],
            'portrait narrower than the target is bounded by height' => [600, 1200, 400, 800],
        ];
    }

    #[DataProvider('targetSizeProvider')]
    public function testThumbSizeKeepsTheAspectRatio(int $width, int $height, int $thumbWidth, int $thumbHeight): void
    {
        $this->createJpeg('pic.jpg', $width, $height);

        $thumb = new Thumb($this->tempDir, 800);
        $this->assertTrue($thumb->thumbImage('pic.jpg'));

        list($w, $h) = getimagesize($this->tempDir . '/' . $thumb->thumbFile);

        $this->assertSame([$thumbWidth, $thumbHeight], [$w, $h]);
    }

    public function testThumbInSubdirectory(): void
    {
        $this->createJpeg('media/photos/pic.jpg', 1600, 1200);

        $thumb = new Thumb($this->tempDir, 800);
        $thumb->thumbImage('media/photos/pic.jpg');

        $this->assertTrue($thumb->thumbed);
        $this->assertStringContainsString('media_photos_pic-800', $thumb->thumbFile);
    }

    public function testPngThumbnailKeepsTransparency(): void
    {
        // createPng() writes a fully transparent image
        $this->createPng('clear.png', 1000, 500);

        $thumb = new Thumb($this->tempDir, 200);
        $thumb->thumbImage('clear.png');

        $png = imagecreatefrompng($this->tempDir . '/' . $thumb->thumbFile);
        $alpha = (imagecolorat($png, 10, 10) >> 24) & 0x7F;

        $this->assertSame(127, $alpha, 'the thumb pixel should stay fully transparent');
    }

    /**
     * Makes a file undeletable: read-only file (Windows) in a read-only folder (POSIX).
     */
    protected function lockFile(string $path): void
    {
        if (function_exists('posix_geteuid') && 0 === posix_geteuid()) {
            $this->markTestSkipped('root ignores file permissions');
        }

        chmod($path, 0444);
        chmod(dirname($path), 0555);
    }
}
