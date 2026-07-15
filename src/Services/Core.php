<?php

namespace App\Services;

use \Composer\Autoload\ClassLoader;
use \ReflectionClass;

class Core {

    public static function getApiKey(): string {

        //shell - set env -> export OPENAI_API_KEY=""

        $ApiKey = getenv('OPENAI_API_KEY');

        if(empty($ApiKey))
            return '';

        return $ApiKey;

    }

    private static function getRootFolderPath(): string {

        return dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);

    }

    public static function getSkil($Name) : string {

        $RootFolderPath = self::getRootFolderPath();
        $SkilPath = $RootFolderPath.'/skills/'.$Name.'.md';

        if(!file_exists($SkilPath))
            return '';

        return file_get_contents($SkilPath);

    }

}