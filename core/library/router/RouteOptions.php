<?php

namespace core\library\router;

class RouteOptions
{
    public function __construct(
        private array $parameters = [],
        private string $prefix = "",
        private string $name = "",
        private string $groupName = "",
        private array $middlewares = [],
        private string $namespace = "",
        private string $defaultNamespace = "",
        private string $customRegex = ""
    ) {
    }

    public function getOption(string $option): string|array
    {
        $this->prefix = empty($this->prefix) ? $this->prefix : "/" . trim($this->prefix, "/");
        return $this->{$option};
    }

    public function setOption(string $option, string|array $value): void
    {
        $this->{$option} = $value;
    }
}
