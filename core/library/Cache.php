<?php

namespace core\library;

class Cache
{
    public static string $path;
    public static string $prefix = "cache_";

    public static function setPath(string $path): void
    {
        self::$path = $path;
    }

    private static function path(string $name): string
    {
        $path = self::$path ?? dirname(__DIR__, 2) . '/app/cache/';
        if (!is_dir($path))
            mkdir($path, 0777, true);

        return $path . self::$prefix . $name . ".json";
    }

    /**
     * @param string $name
     * @param \Closure $callback needs to return a value
     * @param int $expiration in minutes
     * @return mixed Returns the contents of the cache
     */
    public static function make(string $name, \Closure $callback, int $expiration): mixed
    {
        $file = self::path($name);
        if (!file_exists($file) || strtotime("+{$expiration} minutes", filemtime($file)) < time())
            file_put_contents($file, json_encode($callback()));

        return json_decode(file_get_contents($file));
    }

}
