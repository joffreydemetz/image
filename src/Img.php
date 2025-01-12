<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Image;

class Img
{
  public string $basePath;
  public string $srcFile;

  public string $path;
  public string $name;
  public string $ext;
  public string $orientation = '';
  public int $width;
  public int $height;
  public int $type;

  public bool $valid = false;

  public function __construct(string $basePath, string $srcFile)
  {
    $this->basePath = $basePath;
    $this->srcFile = $srcFile;

    if (true === $this->isValid()) {
      $this->valid = true;
      $this->loadFileInfos();
      $this->loadImageInfos();
    }
  }

  public function getFilePath(): string
  {
    return $this->basePath . '/' . $this->srcFile;
    // return $this->basePath . '/' . $this->path . '/' . $this->name . '.' . $this->ext;
  }

  public function getThumbBaseName(): string
  {
    return str_replace(['/', '\\'], '_', $this->path) . '_' . $this->name;
  }

  public function getThumbName(int $size): string
  {
    return $this->getThumbBaseName() . '-' . $size . '.' . $this->ext;
  }

  protected function isValid(): bool
  {
    if ('' === $this->srcFile) {
      return false;
    }

    if (!\file_exists($this->getFilePath())) {
      return false;
    }

    if (!\preg_match("/^image\/.+$/", \mime_content_type($this->getFilePath()))) {
      return false;
    }

    return true;
  }

  protected function loadFileInfos()
  {
    if ('' === $this->srcFile) {
      return;
    }

    $fi = new \SplFileInfo($this->srcFile);
    $this->path = $fi->getPath();
    $this->ext = \strtolower($fi->getExtension());
    $this->name = $fi->getBasename('.' . $this->ext);
  }

  protected function loadImageInfos()
  {
    if ('' === $this->srcFile) {
      return;
    }

    list($width, $height, $type) = \getimagesize($this->basePath . '/' . $this->srcFile);

    $this->type = $type;
    $this->width = $width;
    $this->height = $height;

    if ($this->width < $this->height) {
      $this->orientation = 'portrait';
    } elseif ($this->height < $this->width) {
      $this->orientation = 'landscape';
    }
  }
}
