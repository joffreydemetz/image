<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Image;

class Copyright
{
  public string $basePath;
  public string $originalsFolder;
  public string $watermarkFile;
  public string $copyrightType;

  public function __construct(
    string $basePath,
    string $originalsFolder = 'protect',
    string $watermarkFile = 'nepascopier.png',
    string $copyrightType = 'repeat'
  ) {
    $this->basePath = $basePath;
    $this->originalsFolder = $originalsFolder;
    $this->watermarkFile = $watermarkFile;
    $this->copyrightType = $copyrightType;
  }

  public function protectImage(string $srcFile)
  {
    $this->checkFile($srcFile, ['jpg', 'gif', 'png', 'jpeg']);
    $this->checkFile($this->watermarkFile, ['png']);

    // original files (no watermark) are copied to the protect folder
    // these files let you unwatermark them by copying back the 
    // raw source from the protect folder back to sourcePath

    if (!\file_exists($this->basePath . '/' . $this->originalsFolder . '/' . $srcFile)) {
      // duplicate the original file to keep it safe
      $fs = new \Symfony\Component\Filesystem\Filesystem();
      $fs->copy(
        $this->basePath . '/' . $srcFile,
        $this->basePath . '/' . $this->originalsFolder . '/' . $srcFile,
        true
      );
    }

    try {

      $imagine = new \Imagine\Gd\Imagine();
      $watermark = $imagine->open($this->basePath . '/' . $this->watermarkFile);
      // $watermark->rotate(-35);

      $image = $imagine->open($this->basePath . '/' . $this->originalsFolder . '/' . $srcFile);
      $image = $image->applyMask($image->mask(), 100);
      $size = $image->getSize();
      $wSize = $watermark->getSize();

      if ('repeat' === $this->copyrightType) {
        $x = 0;

        while ($x < $size->getWidth()) {
          $y = 0;

          while ($y < $size->getHeight()) {
            $image->paste($watermark, new \Imagine\Image\Point($x, $y));
            $y += $wSize->getHeight() + 10;
          }

          $x += $wSize->getWidth() + 10;
        }
      }

      $image->save($this->basePath . '/' . $srcFile);
    } catch (\Throwable $e) {
      throw new \Exception('Error watermarking the source' . "\n" . $e->getMessage(), 0, $e);
    }
  }

  public function unprotectImage(string $srcFile)
  {
    $this->checkFile($srcFile, ['jpg', 'gif', 'png', 'jpeg']);

    if (!\file_exists($this->basePath . '/' . $this->originalsFolder . '/' . $srcFile)) {
      // no original source in the protect folder
      // means either it's been deleted
      // OR the original file has never been watermarked through protectImage()
      // OR the originalsFolder has been change but the files not transfered

      throw new \Exception('File cannot be unprotected .. Original file not found');
    }

    try {
      $fs = new \Symfony\Component\Filesystem\Filesystem();

      // copy back the original
      $fs->copy(
        $this->basePath . '/' . $this->originalsFolder . '/' . $srcFile,
        $this->basePath . '/' . $srcFile,
        true
      );

      // remove the backup
      $fs->remove($this->basePath . '/' . $this->originalsFolder . '/' . $srcFile);
    } catch (\Throwable $e) {
      throw new \Exception('Error reverting to the original version of the source' . "\n" . $e->getMessage(), 0, $e);
    }
  }

  protected function checkFile(string $source, array $authExts = ['jpg', 'gif', 'png', 'jpeg'])
  {
    if (!\file_exists($this->basePath . '/' . $source)) {
      throw new \Exception('Source file "' . $source . '" not found !');
    }

    if (!\preg_match("/^image\/.+$/", \mime_content_type($this->basePath . '/' . $source))) {
      throw new \Exception('Source file "' . $source . '" has an invalid mime type !');
    }

    $fi = new \SplFileInfo($source);
    $ext = $fi->getExtension();

    if (!in_array($ext, $authExts)) {
      throw new \Exception('Source file is not an image !');
    }
  }
}
