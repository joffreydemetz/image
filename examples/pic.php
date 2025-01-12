<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
if (empty($basePath)) {
    mkdir(__DIR__ . '/files/');
    $basePath = realpath(__DIR__ . '/files/');
}

echo 'basePath : ' . $basePath . "\n";
echo "\n";

//echo 'Load image media/test.jpg' . "\n";
$img = new \JDZ\Image\Img($basePath, 'media/test.jpg');
//echo json_encode(get_object_vars($img), \JSON_PRETTY_PRINT) . "\n";
//echo "\n";

if (false === $img->valid) {
    echo 'File not found' . "\n";
    exit();
}

echo 'Render pic HTML' . "\n";
$pic = new \JDZ\Image\Pic($img, 'My Image');
$pic->load();
echo (string)$pic . "\n";
echo "\n";

echo 'Add an absolute Url' . "\n";
$pic = new \JDZ\Image\Pic($img, 'My Image', 'https://mywebsite.dot/');
$pic->load();
echo (string)$pic . "\n";
echo "\n";

echo 'Add a relative url' . "\n";
$pic = new \JDZ\Image\Pic($img, 'My Image', '../');
$pic->load();
echo (string)$pic . "\n";
echo "\n";

echo 'Force the image size' . "\n";
$pic = new \JDZ\Image\Pic($img, 'My Image');
$pic->forceSize = true;
$pic->load();
echo (string)$pic . "\n";
echo "\n";

echo 'Add the thumb for lazy loading with Lozad for example' . "\n";
$pic = new \JDZ\Image\Pic($img, 'My Image');
$pic->thumb = 'thumbs/' . $img->getThumbName(400);
$pic->load();
$pic->attrs['class'][] = 'lozad';
echo (string)$pic . "\n";
echo "\n";

echo 'Add some extra attributes' . "\n";
$pic = new \JDZ\Image\Pic($img, 'My Image');
$pic->load();
$pic->dataAttrs['zoom'] = 'true';
$pic->attrs['class'][] = 'class1';
echo (string)$pic . "\n";
echo "\n";

//echo json_encode(get_object_vars($pic), \JSON_PRETTY_PRINT) . "\n";
