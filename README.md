# Image

JDZ Image utilities for thumbnail generation, watermark protection, and HTML rendering.

## Installation

```bash
composer require jdz/image
```

## Requirements

- PHP >= 8.2
- Symfony Filesystem ^7.4
- Symfony Finder ^7.4
- Imagine ^1.3
- `ext-gd` (thumbnails, and Imagine's GD driver for watermarks)

## Usage

### Loading and Displaying Images

```php
use JDZ\Image\Image;

$image = new Image('/path/to/files', 'https://mywebsite.com/');
$image->load('media/photo.jpg', 'media/default.jpg');

$pic = $image->getPic('Photo description');
echo (string)$pic;
// <img src="https://mywebsite.com/media/photo.jpg" alt="Photo description" data-orientation="landscape" />
```

### Lazy Loading with Thumbnails

```php
use JDZ\Image\Image;

$image = new Image('/path/to/files', 'https://mywebsite.com/', 'thumbs');
$image->lazy = true;
$image->targetWidth = 800;
$image->cacheLife = 3600;
$image->load('media/photo.jpg');

$pic = $image->getPic('Photo');
echo (string)$pic;
// src points to thumbnail, data-src points to original
```

### Creating Thumbnails

```php
use JDZ\Image\Thumb;

$thumb = new Thumb('/path/to/files', 800, 'thumbs', 3600);
$created = $thumb->thumbImage('media/photo.jpg');

echo $thumb->thumbFile; // 'thumbs/media_photo-800.jpg'
```

### Watermark Protection

```php
use JDZ\Image\Copyright;

$copyright = new Copyright('/path/to/files', 'protect', 'watermark.png', 'repeat');

// Apply watermark (original backed up to protect/ folder)
$copyright->protectImage('media/photo.jpg');

// Restore original
$copyright->unprotectImage('media/photo.jpg');
```

### Filesystem Setup

```php
use JDZ\Image\Fs;

$fs = new Fs('/path/to/files', 'media', 'thumbs', 'protect');
$fs->check(); // Creates media/, thumbs/, protect/ folders if missing
```

### Image Info

```php
use JDZ\Image\Img;

$img = new Img('/path/to/files', 'media/photo.jpg');

if ($img->valid) {
    echo $img->width;       // 1600
    echo $img->height;      // 1200
    echo $img->orientation;  // 'landscape'
    echo $img->ext;          // 'jpg'
}
```

## Testing

```bash
composer test
# or
vendor/bin/phpunit
```

## Changelog

- **1.0.4** - `getPic()` in lazy mode no longer crashes on a picture too small for a thumb; removing the thumbs of `photo.jpg` no longer removes those of `photo-2.jpg`; a BMP / WebP source is a clear "Unsupported image type" error; `Copyright` accepts an upper-case extension.
- **1.0.3** - PNG thumbnails keep their transparency (the image type was compared as a string, so the alpha channel was never saved); `ext-gd` declared.
- **1.0.2** - PHP >= 8.2, Symfony ^7.4; unit test suite. No code change.
- **1.0.1** - Symfony components ^7.2; the `Build` class is removed.
- **1.0.0** - Initial release.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
