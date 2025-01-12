<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
if (empty($basePath)) {
    mkdir(__DIR__ . '/files/');
    $basePath = realpath(__DIR__ . '/files/');
}

echo 'basePath : ' . $basePath . "\n";
echo 'Remove thumbs for media/test.jpg' . "\n";

try {
    $thumb = new \JDZ\Image\Thumb($basePath, 800, 'thumbs', 6000);
    if (true === $thumb->unthumbImage('media/test.jpg')) {
        echo 'Thumbs deleted succesfully !';
    } else {
        echo 'Thumbs not all deleted succesfully !';
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
