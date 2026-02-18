<?php

namespace AllInOneSeeder;

class Autoloader
{
    private const PREFIX = 'AllInOneSeeder\\';

    public static function register(): void
    {
        spl_autoload_register([static::class, 'autoload']);
    }

    private static function autoload(string $class): void
    {
        if (strpos($class, self::PREFIX) !== 0) {
            return;
        }

        $relativeClass = substr($class, strlen(self::PREFIX));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        $file = AIOS_PLUGIN_PATH . 'includes' . DIRECTORY_SEPARATOR . $relativePath;

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
