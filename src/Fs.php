<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Image;

use Symfony\Component\Filesystem\Filesystem;

class Fs
{
    public string $basePath;
    public string $mediasFolder;
    public string $thumbsFolder;
    public string $copyrightsFolder;

    public function __construct(
        string $basePath,
        string $mediasFolder = 'media',
        string $thumbsFolder = 'thumbs',
        string $copyrightsFolder = 'protect'
    ) {
        $this->basePath = $basePath;
        $this->mediasFolder = $mediasFolder;
        $this->thumbsFolder = $thumbsFolder;
        $this->copyrightsFolder = $copyrightsFolder;
    }

    public function check()
    {
        if ('' === $this->basePath) {
            throw new \LogicException('basePath cannot be empty');
        }

        if (!is_dir($this->basePath)) {
            throw new \LogicException('basePath "' . $this->basePath . '" does not exist');
        }

        $fs = new Filesystem();

        if (!$fs->exists($this->basePath . '/' . $this->mediasFolder . '/')) {
            echo 'create folder /' . $this->mediasFolder . '/' . "\n";
            $fs->mkdir($this->basePath . '/' . $this->mediasFolder . '/');
        }

        if (!$fs->exists($this->basePath . '/' . $this->thumbsFolder . '/')) {
            echo 'create folder /' . $this->thumbsFolder . '/' . "\n";
            $fs->mkdir($this->basePath . '/' . $this->thumbsFolder . '/');
        }

        if (!$fs->exists($this->basePath . '/' . $this->copyrightsFolder . '/')) {
            echo 'create folder /' . $this->copyrightsFolder . '/' . "\n";
            $fs->mkdir($this->basePath . '/' . $this->copyrightsFolder . '/');
        }
    }
}
