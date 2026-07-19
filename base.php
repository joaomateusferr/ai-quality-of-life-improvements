<?php

require __DIR__ . '/vendor/autoload.php';

use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;

$_SERVER['PROJECT_ROOT'] = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);
$DotEnv = Dotenv::createImmutable($_SERVER['PROJECT_ROOT']);
$DotEnv->load();

function getSkil(string $Name) : string {

    $SkilPath = $_SERVER['PROJECT_ROOT'].'/skills/'.$Name.'.md';

    if(!file_exists($SkilPath))
        return '';

    return file_get_contents($SkilPath);

}