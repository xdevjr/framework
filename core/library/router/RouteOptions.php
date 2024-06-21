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
        if (!array_is_list($this->parameters))
            throw new \Exception("The parameters cannot be an associative array!");

        if (!array_is_list($this->middlewares))
            throw new \Exception("The middlewares cannot be an associative array!");
    }

    public static function create(
        array $parameters = [],
        string $prefix = "",
        string $name = "",
        string $groupName = "",
        array $middlewares = [],
        string $namespace = "",
        string $defaultNamespace = "",
        string $customRegex = ""
    ): static {
        return new static($parameters, $prefix, $name, $groupName, $middlewares, $namespace, $defaultNamespace, $customRegex);
    }

    public function getOption(string $option): string|array
    {
        $this->prefix = empty($this->prefix) ? $this->prefix : "/" . trim($this->prefix, "/");
        return $this->{$option};
    }

    public function setOption(string $option, string|array $value): void
    {
        if (!property_exists($this, $option))
            throw new \Exception("The option {$option} does not exist!");

        $this->{$option} = $value;
    }

    public function merge(RouteOptions ...$routeOptions): static
    {
        foreach ($routeOptions as $routeOption) {
            $this->parameters = array_merge($this->parameters, $routeOption->parameters);
            $this->prefix .= $routeOption->prefix;
            $this->name = $routeOption->name ?: $this->name;
            $this->groupName .= empty($this->groupName) ? $routeOption->groupName : "." . $routeOption->groupName;
            $this->middlewares = array_merge($this->middlewares, $routeOption->middlewares);
            $this->namespace = $routeOption->namespace;
            $this->defaultNamespace = $routeOption->defaultNamespace ?: $this->defaultNamespace;
            $this->customRegex = $routeOption->customRegex;
        }

        return $this;
    }

    public function clearNonGroupOptions(): static
    {
        $this->parameters = [];
        $this->name = "";
        $this->defaultNamespace = "";
        $this->customRegex = "";

        return $this;
    }
}
