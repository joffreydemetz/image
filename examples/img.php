<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
if (empty($basePath)) {
    mkdir(__DIR__ . '/files/');
    $basePath = realpath(__DIR__ . '/files/');
}

echo 'basePath : ' . $basePath . "\n";
echo 'Load and check image media/test.jpg' . "\n";
echo "\n";

echo 'Valid image' . "\n";
$img = new \JDZ\Image\Img($basePath, 'media/test.jpg');
echo json_encode(get_object_vars($img), \JSON_PRETTY_PRINT) . "\n";
echo "\n";

echo 'Invalid image' . "\n";
$img = new \JDZ\Image\Img($basePath, 'media/tst.jpg');
echo json_encode(get_object_vars($img), \JSON_PRETTY_PRINT);
echo "\n";
