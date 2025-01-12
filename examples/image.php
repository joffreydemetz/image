<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
if (empty($basePath)) {
    mkdir(__DIR__ . '/files/');
    $basePath = realpath(__DIR__ . '/files/');
}

echo 'basePath : ' . $basePath . "\n";
echo "\n";

$baseUrl = 'https://mywebsite.dot/';
$thumbsFolder = 'thumbs';
$cacheTime = 60;
$thumbMaxWidth = 800;
$defaultImage = 'images/default.jpg';

function displayImage(\JDZ\Image\Image $image)
{
    if (false === $image->valid) {
        echo 'Invalid image' . "\n";
        echo json_encode(get_object_vars($image), \JSON_PRETTY_PRINT) . "\n";
    } else {
        $pic = $image->getPic('My pic');
        echo (string)$pic . "\n";
        echo json_encode(get_object_vars($pic), \JSON_PRETTY_PRINT) . "\n";
    }
}

echo 'Load valid image media/test.jpg including lazy loading (create thumb if inexistant)' . "\n";
$image = new \JDZ\Image\Image($basePath, $baseUrl, $thumbsFolder);
$image->lazy = true;
$image->cacheLife = $cacheTime;
$image->targetWidth = $thumbMaxWidth;
$image->load('media/test.jpg', $defaultImage);
displayImage($image);
echo "\n";

echo 'Load invalid image media/tst.jpg including lazy loading (ignore thumb if default)' . "\n";
$image = new \JDZ\Image\Image($basePath, $baseUrl, $thumbsFolder);
$image->lazy = true;
$image->cacheLife = $cacheTime;
$image->targetWidth = $thumbMaxWidth;
$image->load('media/tst.jpg', $defaultImage);
displayImage($image);
echo "\n";

echo 'Load invalid image media/tst.jpg with no default' . "\n";
$image = new \JDZ\Image\Image($basePath, $baseUrl);
$image->load('media/tst.jpg');
displayImage($image);
echo "\n";

echo 'Load invalid image media/tst.jpg with default' . "\n";
$image = new \JDZ\Image\Image($basePath, $baseUrl);
$image->load('media/tst.jpg', $defaultImage);
displayImage($image);
echo "\n";
