<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Image;

use JDZ\Image\Img;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class Thumb
{
  public string $basePath;
  public string $thumbsFolder;
  public int $cacheLife;
  public int $targetWidth;
  public int $targetHeight = 0;
  public ?string $thumbFile = null;
  public bool $thumbed = false;

  public function __construct(
    string $basePath,
    int $targetWidth = 800,
    string $thumbsFolder = 'thumbs',
    int $cacheLife = 0
  ) {
    $this->basePath = $basePath;
    $this->targetWidth = $targetWidth;
    $this->thumbsFolder = $thumbsFolder;
    $this->cacheLife = $cacheLife;
  }

  public function thumbImage(string $srcFile, bool $force = false): bool
  {
    $img = new Img(
      $this->basePath,
      $srcFile
    );

    if (false === $img->valid) {
      throw new \Exception('Source file "' . $srcFile . '" is not a valid image !');
    }

    list($targetWidth, $targetHeight) = $this->getTargetSize($img->width, $img->height);

    // image is small enough
    if ($targetWidth >= $img->width && $targetHeight >= $img->height) {
      $this->thumbed = false;
      $this->thumbFile = $srcFile;
      return false;
    }

    $thumbName = $img->getThumbName($this->targetWidth);
    $thumbFullPath = $this->basePath . '/' . $this->thumbsFolder . '/' . $thumbName;

    // check existing thumb
    if (\file_exists($thumbFullPath)) {
      $this->thumbed = true;
      $this->thumbFile = $this->thumbsFolder . '/' . $thumbName;

      if (false === $force) {
        // always valid
        if (0 === $this->cacheLife) {
          // No clean cache
          return false;
        }

        // check thumb modified time
        if ((time() - \filemtime($thumbFullPath)) < $this->cacheLife) {
          // still valid
          return false;
        }
      }

      // remove file
      try {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove($thumbFullPath);

        if (\file_exists($thumbFullPath)) {
          throw new \Exception('Failed to delete the thumb file');
        }

        $this->thumbed = false;
        $this->thumbFile = null;
      } catch (\Throwable $e) {
        throw new \Exception('Error deleting the thumb file ' . "\n" . $e->getMessage());
      }
    }

    try {
      $this->doCreateThumb($img->getFilePath(), $thumbFullPath, $targetWidth, $targetHeight, $img->type);

      if (!file_exists($thumbFullPath)) {
        throw new \Exception('Thumb file not created');
      }
    } catch (\Throwable $e) {
      throw new \Exception('Error creating the thumb file ' . "\n" . $e->getMessage());
    }

    $this->thumbed = true;
    $this->thumbFile = $this->thumbsFolder . '/' . $thumbName;

    return true;
  }

  public function unthumbImage(string $srcFile): bool
  {
    $img = new Img(
      $this->basePath,
      $srcFile
    );

    if (false === $img->valid) {
      throw new \Exception('Source file "' . $srcFile . '" is not a valid image !');
    }

    $thumbs = $this->listThumbs($img->getThumbBaseName());

    if ($thumbs) {
      $fs = new Filesystem();

      foreach ($thumbs as $thumb) {
        try {
          $fs->remove($thumb);
        } catch (\Throwable $e) {
        }
      }

      $thumbs = $this->listThumbs($img->getThumbBaseName());
    }

    return empty($thumbs);
  }

  protected function listThumbs(string $baseThumbName): array
  {
    $finder = new Finder();
    $finder->files()
      ->name('/^' . preg_quote($baseThumbName) . '-.*$/')
      ->in($this->basePath . '/' . $this->thumbsFolder);

    $thumbs = [];
    if ($finder->hasResults()) {
      foreach ($finder as $file) {
        $thumbs[] = $file->getRealPath();
      }
    }

    return $thumbs;
  }

  protected function doCreateThumb(
    string $srcFulPath,
    string $thumbFullPath,
    int $targetWidth,
    int $targetHeight,
    string $imageType
  ) {
    if ($imageType == \IMAGETYPE_JPEG) {
      $image = \imagecreatefromjpeg($srcFulPath);
    } elseif ($imageType == \IMAGETYPE_PNG) {
      $image = \imagecreatefrompng($srcFulPath);
    } elseif ($imageType == \IMAGETYPE_GIF) {
      $image = \imagecreatefromgif($srcFulPath);
    }

    $x = \imagesx($image);
    $y = \imagesy($image);

    $thumbnail = \imagecreatetruecolor($targetWidth, $targetHeight);

    if (\in_array($imageType, [\IMAGETYPE_GIF, \IMAGETYPE_PNG])) {
      \imagecolortransparent($thumbnail, \imagecolorallocatealpha($thumbnail, 255, 255, 255, 127));

      if (\IMAGETYPE_PNG === $imageType) {
        \imagealphablending($thumbnail, false);
        \imagesavealpha($thumbnail, true);
      }
    }

    \imagecopyresampled(
      $thumbnail,
      $image,
      0,
      0,
      0,
      0,
      $targetWidth,
      $targetHeight,
      $x,
      $y
    );

    if ($imageType == \IMAGETYPE_JPEG) {
      \imagejpeg($thumbnail, $thumbFullPath, 100);
    } elseif ($imageType == \IMAGETYPE_PNG) {
      \imagepng($thumbnail, $thumbFullPath, 0);
    } elseif ($imageType == \IMAGETYPE_GIF) {
      \imagegif($thumbnail, $thumbFullPath);
    }
  }

  protected function getTargetSize(int $width, int $height): array
  {
    $targetWidth = $this->targetWidth;
    $targetHeight = $this->targetHeight;

    $ratio = $width / $height;

    if ($width > $targetWidth) {
      $targetHeight = \floor($targetWidth / $ratio);
    } else {
      $targetHeight = $targetWidth;
      $targetWidth = \floor($targetWidth * $ratio);
    }

    return [$targetWidth, $targetHeight];
  }
}
