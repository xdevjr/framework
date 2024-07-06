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

    public static function get(?string $key = null): array|string
    {
        if ($key) {
            if (str_contains($key, "?")) {
                $key = substr($key, 1);
                $wildcard = self::$wildcards[$key];
                $wildcard = $wildcard ? "?({$wildcard})?" : "";
            } else {
                $wildcard = self::$wildcards[$key] ?? "";
            }

            return $wildcard;
        }

        return self::$wildcards;
    }

}
