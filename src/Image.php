<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Image;

use JDZ\Image\Thumb;
use JDZ\Image\Pic;

class Image
{
  public string $basePath;
  public string $baseUrl;
  public string $thumbsFolder = 'thumbs';
  public bool $lazy = false;
  public int $cacheLife = 0;
  public int $targetWidth = 800;

  public ?string $default = null;
  public ?Img $source = null;
  public ?string $thumb = null;
  public bool $exists = false;
  public bool $valid = false;

  public function __construct(string $basePath, string $baseUrl, string $thumbsFolder = 'thumbs')
  {
    $this->basePath = $basePath;
    $this->baseUrl = $baseUrl;
    $this->thumbsFolder = $thumbsFolder;
  }

  public function load(string $srcFile, ?string $default = null)
  {
    $img = new Img($this->basePath, $srcFile);

    if (true === $img->valid) {
      $this->source = $img;
      $this->valid = true;
      $this->exists = true;

      if (true === $this->lazy) {
        $thumbName = $img->getThumbName($this->targetWidth);
        $thumbFullPath = $this->basePath . '/' . $this->thumbsFolder . '/' . $thumbName;

        if (true === \file_exists($thumbFullPath)) {
          $this->thumb = $this->thumbsFolder . '/' . $thumbName;
        } else {
          $thumb = new Thumb(
            $this->basePath,
            $this->targetWidth,
            $this->thumbsFolder,
            $this->cacheLife
          );

          $thumb->thumbImage($srcFile);

          if (true === $thumb->thumbed) {
            $this->thumb = $thumb->thumbFile;
          }
        }
      }

      return $this;
    }

    $this->lazy = false;

    if ($default) {
      $img = new Img($this->basePath, $default);
      if (true === $img->valid) {
        $this->valid = true;
        $this->source = $img;
      }
    }

    return $this;
  }

  public function getPic(string $alt = '', string $style = ''): Pic
  {
    if (!$this->source) {
      throw new \Exception('Cannot export an invalid image ..');
    }

    $baseUrl = $this->baseUrl;
    $img = $this->source;

    $pic = new Pic($img, $alt, $baseUrl);

    if (true === $this->lazy) {
      $pic->thumb = $this->thumb;
    }

    $pic->load();

    if (false === $this->exists) {
      $pic->attrs['class'][] = 'default';
    }

    if ($style) {
      $pic->attrs['style'] = $style;
    }

    return $pic;
  }
}
