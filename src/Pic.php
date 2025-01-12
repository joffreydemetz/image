<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Image;

class Pic
{
  public string $src;
  public string $alt;
  public string $baseUrl;

  public string $orientation;
  public int $width;
  public int $height;

  public string $thumb = '';
  public string $style = '';

  public array $dataAttrs = [];
  public array $attrs = [];
  public bool $forceSize = false;

  public function __construct(Img $img, string $alt = '', string $baseUrl = '')
  {
    $this->src = $img->srcFile;
    $this->orientation = $img->orientation;
    $this->width = $img->width;
    $this->height = $img->height;
    $this->alt = $alt;
    $this->baseUrl = $baseUrl;
  }

  public function __toString(): string
  {
    return $this->toString();
  }

  public function load()
  {
    if ('' !== $this->thumb) {
      $this->attrs['src'] = $this->baseUrl . $this->thumb;
      $this->dataAttrs['src'] = $this->baseUrl . $this->src;
    } else {
      $this->attrs['src'] = $this->baseUrl . $this->src;
    }

    $this->attrs['alt'] = $this->alt;

    if (true === $this->forceSize) {
      $this->attrs['width'] = $this->width;
      $this->attrs['height'] = $this->height;
    }

    $this->attrs['class'] = [];

    if ('' !== $this->orientation) {
      $this->dataAttrs['orientation'] = $this->orientation;
    }

    return $this;
  }

  public function toAttributes(): array
  {
    $attrs = $this->attrs;
    $attrs['class'] = $attrs['class'] ? implode(' ', $attrs['class']) : '';
    if (empty($attrs['class'])) {
      unset($attrs['class']);
    }
    foreach ($this->dataAttrs as $key => $value) {
      $attrs['data-' . $key] = $value;
    }

    $attrs = \array_map(function ($value) {
      if (true === $value) {
        $value = 'true';
      } elseif (false === $value) {
        $value = 'false';
      } elseif ('' !== $value) {
        $value = trim($value);
        $value = str_replace('"', '\"', $value);
      } else {
        $value = '';
      }
      return $value;
    }, $attrs);

    return $attrs;
  }

  public function toString(): string
  {
    $attrs = $this->toAttributes();

    $str = '<img';
    foreach ($attrs as $key => $value) {
      $str .= ' ' . $key . '="' . $value . '"';
    }
    $str .= ' />';
    return $str;
  }
}
