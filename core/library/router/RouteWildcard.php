<?php

namespace core\library\router;

class RouteWildcard
{
    private array $wildcards = [
        "num" => "[0-9]+",
        "alpha" => "[a-z]+",
        "any" => "[a-z0-9\-]+",
    ];

    public function add(string $name, string $pattern): void
    {
        if (!in_array($name, $this->wildcards))
            $this->wildcards[$name] = $pattern;
    }

    public function get(): array
    {
        return $this->wildcards;
    }

}
