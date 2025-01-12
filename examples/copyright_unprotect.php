<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$basePath = realpath(__DIR__ . '/files/');
if (empty($basePath)) {
    mkdir(__DIR__ . '/files/');
    $basePath = realpath(__DIR__ . '/files/');
}

echo 'basePath : ' . $basePath . "\n";
echo 'Unprotect image media/test.jpg' . "\n";

try {
    $copyright = new \JDZ\Image\Copyright($basePath, 'protect', 'nepascopier.png', 'repeat');
    $copyright->unprotectImage('media/test.jpg');
    echo 'Image unprotected succesfully !';
} catch (\Exception $e) {
    echo $e->getMessage();
}
