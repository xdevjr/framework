<?php

namespace core\library\router;

abstract class RouteWildcard
{
    private static array $wildcards = [
        "num" => "[0-9]+",
        "alpha" => "[a-z]+",
        "any" => "[a-z0-9\-]+",
    ];

    public static function add(string $name, string $pattern): void
    {
        self::$wildcards[$name] ??= $pattern;
    }

    public static function get(): array
    {
        return self::$wildcards;
    }

}
