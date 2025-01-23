# JDZ Image Utilities

JDZ Image Utilities is a PHP library that provides various utilities for handling images, including thumbnail creation, image protection, and more.

## Installation

To install the library, use Composer:

```sh
composer require jdz/image
```

## Requirements
PHP >= 8.1
Symfony Filesystem ^6.4
Symfony Finder ^6.4
Imagine ^1.3

## Usage

*Protecting an Image

To protect an image with a watermark:

```php
<?php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$coyright = new \JDZ\Image\Copyright($basePath, 'protect', 'nepascopier.png', 'repeat');
$coyright->protectImage('media/test.jpg');
```

*Unprotecting an Image

To remove the watermark from an image:

```php
<?php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$coyright = new \JDZ\Image\Copyright($basePath, 'protect', 'nepascopier.png', 'repeat');
$coyright->unprotectImage('media/test.jpg');
```

*Creating a Thumbnail

To create a thumbnail of an image:

```php
<?php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$thumb = new \JDZ\Image\Thumb($basePath, 200, 'thumbs', 120);
$thumb->thumbImage('media/test.jpg');
```

*Loading and Displaying an Image

To load and display an image with lazy loading and caching:

```php
<?php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$baseUrl = 'https://mywebsite.dot/';
$thumbsFolder = 'thumbs';
$cacheTime = 60;
$thumbMaxWidth = 800;
$defaultImage = 'images/default.jpg';

$image = new \JDZ\Image\Image($basePath, $baseUrl, $thumbsFolder);
$image->lazy = true;
$image->cacheLife = $cacheTime;
$image->targetWidth = $thumbMaxWidth;
$image->load('media/test.jpg', $defaultImage);

$pic = $image->getPic('My pic');
echo (string)$pic;
```

I apologize for the inconvenience. Let's update the 

README.md

 file with the new content:

```markdown


# JDZ Image Utilities

JDZ Image Utilities is a PHP library that provides various utilities for handling images, including thumbnail creation, image protection, and more.

## Installation

To install the library, use Composer:

```sh
composer require jdz/image
```

## Requirements

- PHP >= 8.1
- Symfony Filesystem ^6.4
- Symfony Finder ^6.4
- Imagine ^1.3

## Usage

### Protecting an Image

To protect an image with a watermark:

```php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$coyright = new \JDZ\Image\Copyright($basePath, 'protect', 'nepascopier.png', 'repeat');
$coyright->protectImage('media/test.jpg');
```

### Unprotecting an Image

To remove the watermark from an image:

```php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$coyright = new \JDZ\Image\Copyright($basePath, 'protect', 'nepascopier.png', 'repeat');
$coyright->unprotectImage('media/test.jpg');
```

### Creating Thumbnails

To create a thumbnail for an image:

```php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$thumb = new \JDZ\Image\Thumb($basePath, 200, 'thumbs', 120);
$thumb->thumbImage('media/test.jpg');
```

### Loading and Displaying Images

To load and display an image:

```php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
$baseUrl = 'https://mywebsite.dot/';
$thumbsFolder = 'thumbs';
$cacheTime = 60;
$thumbMaxWidth = 800;
$defaultImage = 'images/default.jpg';

$image = new \JDZ\Image\Image($basePath, $baseUrl, $thumbsFolder);
$image->lazy = true;
$image->cacheLife = $cacheTime;
$image->targetWidth = $thumbMaxWidth;
$image->load('media/test.jpg', $defaultImage);

$pic = $image->getPic('My pic');
echo (string)$pic;
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

- Joffrey Demetz - [joffreydemetz.com](https://joffreydemetz.com)

For more examples, see the examples directory.
