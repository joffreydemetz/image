<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
if (empty($basePath)) {
    mkdir(__DIR__ . '/files/');
    $basePath = realpath(__DIR__ . '/files/');
}

echo 'basePath : ' . $basePath . "\n";
echo "\n";

try {
    echo 'Create thumb for media/test.jpg ' . "\n";
    echo 'Thumb will be valid in cache for 120 seconds' . "\n";
    echo "\n";

    echo 'Create a 200px thumb' . "\n";

    $thumb = new \JDZ\Image\Thumb($basePath, 200, 'thumbs', 120);
    if (true === $thumb->thumbImage('media/test.jpg')) {
        echo 'Thumb created succesfully !' . "\n";
    } else if (true === $thumb->thumbed) {
        echo 'Thumb already exists and still valid !' . "\n";
    }

    echo "\n";

    echo 'Create a 400px thumb' . "\n";

    $thumb = new \JDZ\Image\Thumb($basePath, 400, 'thumbs', 120);
    if (true === $thumb->thumbImage('media/test.jpg')) {
        echo 'Thumb created succesfully !' . "\n";
    } else if (true === $thumb->thumbed) {
        echo 'Thumb already exists and still valid !' . "\n";
    }
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
}
