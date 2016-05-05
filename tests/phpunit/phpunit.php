<?php


require __DIR__.'/Autoload.php';

$Autoload = new \Rundiz\Upload\Tests\Autoload();
$Autoload->addNamespace('Rundiz\\Upload\\Tests', __DIR__);
$Autoload->addNamespace('Rundiz\\Upload', dirname(dirname(__DIR__)).'/Rundiz/Upload');
$Autoload->register();