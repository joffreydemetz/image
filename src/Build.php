<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Image;

class Build
{
  public string $source;
  public string $sourcePath;
  public string $thumbsPath;
  public int $cacheLife;

  public string $default = '';
  public string $alt = '';
  public string $style = '';
  public string $property = '';
  public string $dataSrc = '';
  public string $urlBase = '';

  public bool $exists = true;
  public bool $valid = false;
  public bool $zoom = false;
  public bool $lazy = false;
  public bool $isDefault = false;
  public int $targetWidth = 800;
  public int $targetHeight = 0;
  public string $orientation = '';

  public $type;
  public $width;
  public $height;
  public $srcFullPath;
  public $srcPath;
  public $srcName;
  public $srcExt;

  public $thumb = '';
  // public $thumbName;
  // public $thumbTime;

  public $x;
  public $y;

  public function __construct(string $source)
  {
    $this->source = $source;
  }

  public function isValid(): bool
  {
    return true === $this->valid;
  }

  public function load()
  {
    // check source
    // if empty and default
    //   return new DefaultImg
    // if empty and no default
    //   return EmptyImg

    // set file infos
    // set image infos

    $srcFullPath = $this->sourcePath . $this->source;
    $srcFullPath = normalizePath($srcFullPath);

    // debug($srcFullPath, false);

    if ('' === $this->source || false === @file_exists($srcFullPath)) {
      $this->defaultSource();
      return $this;
    }

    $mime = \mime_content_type($srcFullPath);

    if (!preg_match("/^image\/.+$/", $mime)) {
      $this->defaultSource();
      return $this;
    }

    $fi = new \SplFileInfo($this->source);
    $ext = $fi->getExtension();

    $this->valid = true;
    $this->srcFullPath = $srcFullPath;
    $this->srcPath = $fi->getPath();
    $this->srcName = $fi->getBasename('.' . $ext);
    $this->srcExt  = $ext;

    list($srcWidth, $srcHeight, $type) = \getimagesize($this->srcFullPath);

    $this->type = $type;
    $this->width = $srcWidth;
    $this->height = $srcHeight;

    if ($this->width < $this->height) {
      $this->orientation = 'portrait';
    } elseif ($this->height < $this->width) {
      $this->orientation = 'landscape';
    }

    // if ( !in_array($type, [ \IMAGETYPE_JPEG, \IMAGETYPE_PNG, \IMAGETYPE_GIF ]) ){
    if (!in_array($this->type, [\IMAGETYPE_JPEG, \IMAGETYPE_PNG])) {
      $this->lazy = false;
      return $this;
    }

    // $this->smallEnough = false;

    if (true === $this->lazy) {
      $this->lazySource();
    }

    return $this;
  }

  public function getThumbOrSrc(bool $version = false): string
  {
    if (false === $this->valid) {
      return '';
    }

    // if ( true === $this->lazy && '' !== $this->thumb ){
    if (true === $this->lazy) {
      $value = $this->urlBase . $this->thumb;
    } else {
      $value = $this->urlBase . $this->source;
    }

    if (true === $version) {
      $value .= '?v=' . uniqid();
    }

    return $value;
  }

  public function getAdminSelection(): \stdClass
  {
    $pic = new \stdClass;
    $pic->valid = $this->valid;
    if (true === $this->valid) {
      $pic->source = $this->source;
      $pic->src = $this->urlBase . $this->source;
      $pic->thumb = '' === $this->thumb ? '' : $this->urlBase . $this->thumb;
      $pic->orientation = $this->orientation;
    }
    return $pic;
  }

  public function export(): \stdClass
  {
    $pic = new \stdClass;

    $pic->valid  = $this->valid;
    $pic->exists = $this->exists;
    $pic->src    = '';
    $pic->attrs  = [];

    if (true === $this->valid) {
      // $pic->source  = $this->source;
      $pic->path  = $this->sourcePath . $this->source;
      $pic->lazy  = $this->lazy;
      $pic->zoom  = $this->zoom;
      $pic->orientation = $this->orientation;
      $pic->src   = $this->urlBase . $this->source;
      $pic->thumb = $this->urlBase . $this->thumb;
      $pic->width = $this->width;
      $pic->height = $this->height;

      $pic->attrs['alt'] = $this->alt;
      $pic->attrs['class'] = [];

      if (true === $this->isDefault) {
        $pic->attrs['class'] = 'default';
      }

      if ('' !== $this->orientation) {
        $pic->attrs['data-orientation'] = $this->orientation;
      }

      if (true === $this->property) {
        $pic->attrs['property'] = $this->property;
      }

      if (true === $this->zoom) {
        $pic->attrs['data-zoom'] = $this->source;
      }

      if (true === $this->lazy) {
        $pic->attrs['src'] = $this->urlBase . $this->thumb;
        $pic->attrs['data-src'] = $this->urlBase . $this->source;
      } else {
        $pic->attrs['src'] = $this->urlBase . $this->source;
      }

      if ('' !== $this->style) {
        $pic->attrs['style'] = $this->style;
      }

      if (empty($pic->attrs['class'])) {
        unset($pic->attrs['class']);
      }
    }

    return $pic;
  }

  protected function defaultSource()
  {
    // $this->source = $this->default;
    $this->exists = false;
    $this->zoom = false;
    $this->copyright = false;
    // $this->targetWidth = 300;
    // $this->alt = 'Missing file';

    $defaultSrcPath = $this->sourcePath . $this->default;
    $defaultSrcPath = normalizePath($defaultSrcPath);

    if ('' !== $this->default && true === @file_exists($defaultSrcPath)) {
      $this->source = $this->default;
      $this->isDefault = true;
      $this->valid = true;

      list($srcWidth, $srcHeight) = getimagesize($defaultSrcPath);

      if ($srcWidth < $srcHeight) {
        $this->orientation = 'portrait';
      } elseif ($srcHeight < $srcWidth) {
        $this->orientation = 'landscape';
      }
    } else {
      $this->default = '';
      $this->lazy = false;
      $this->valid = false;
    }
  }

  protected function lazySource()
  {
    if (false === @file_exists($this->thumbsPath)) {
      $fs = new \Symfony\Component\Filesystem\Filesystem();
      $fs->mkdir($this->thumbsPath);
    }

    $thumbName = str_replace(['/', '\\'], '_', $this->srcPath) . '_' . $this->srcName . '-thumb.' . $this->srcExt;
    $thumbFullPath = $this->thumbsPath . $thumbName;
    $thumbFullPath = normalizePath($thumbFullPath);

    try {
      if (true === @file_exists($thumbFullPath)) {
        if (0 === $this->cacheLife) {
          // No clean cache
          $this->thumb = 'thumbs/' . $thumbName;
          return;
        }

        // check thumbTime
        if ((time() - filemtime($thumbFullPath)) < $this->cacheLife) {
          // still valid
          $this->thumb = 'thumbs/' . $thumbName;
          return;
        }

        // remove from cache
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove($thumbFullPath);
      }

      if ($this->type == \IMAGETYPE_JPEG) {
        $image = imagecreatefromjpeg($this->srcFullPath);
      } elseif ($this->type == \IMAGETYPE_PNG) {
        $image = imagecreatefrompng($this->srcFullPath);
      } elseif ($this->type == \IMAGETYPE_GIF) {
        $image = imagecreatefromgif($this->srcFullPath);
      }

      $this->x = imagesx($image);
      $this->y = imagesy($image);

      $ratio = $this->x / $this->y;

      if ($this->x < $this->y) {
        $this->orientation = 'portrait';
      } elseif ($this->y < $this->x) {
        $this->orientation = 'landscape';
      }

      if ($this->x > $this->y) {
        $this->targetHeight = floor($this->targetWidth / $ratio);
      } else {
        $this->targetHeight = $this->targetWidth;
        $this->targetWidth = floor($this->targetWidth * $ratio);
      }

      // image is small enough
      if ($this->targetWidth >= $this->x && $this->targetHeight >= $this->y) {
        // $this->thumb = '';
        $this->lazy = false;
        return;
      }

      $thumbnail = imagecreatetruecolor($this->targetWidth, $this->targetHeight);

      if (in_array($this->type, [\IMAGETYPE_GIF, \IMAGETYPE_PNG])) {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 255, 255, 255, 127));

        if (\IMAGETYPE_PNG === $this->type) {
          imagealphablending($thumbnail, false);
          imagesavealpha($thumbnail, true);
        }
      }

      imagecopyresampled(
        $thumbnail,
        $image,
        0,
        0,
        0,
        0,
        $this->targetWidth,
        $this->targetHeight,
        $this->x,
        $this->y
      );

      if ($this->type == \IMAGETYPE_JPEG) {
        imagejpeg($thumbnail, $thumbFullPath, 100);
      } elseif ($this->type == \IMAGETYPE_PNG) {
        imagepng($thumbnail, $thumbFullPath, 0);
      } elseif ($this->type == \IMAGETYPE_GIF) {
        imagegif($thumbnail, $thumbFullPath);
      }

      if (false === @file_exists($thumbFullPath)) {
        // $this->thumb = '';
        $this->lazy = false;
        return;
      }

      $this->thumb = 'thumbs/' . $thumbName;
      // $this->thumbFullPath = $thumbFullPath;

    } catch (\Exception $e) {
    }
  }
}
