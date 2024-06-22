<?php

namespace core\library\container;

class Application
{
    private static Container $container;

    public static function resolve(Container $container): void
    {
        self::$container = $container;
    }

    public static function make(string $key): mixed
    {
        return self::$container->get($key);
    }

    public static function call(string $key, string $method, array $parameters = []): void
    {
        self::$container->call($key, $method, $parameters);
    }
}
