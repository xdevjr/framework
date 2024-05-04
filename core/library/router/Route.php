<?php

namespace core\library\router;

class Route
{
    private ?string $routeName = null;
    public string $path;

    public function __construct(
        public string $uri,
        public \Closure|string $callback,
        public array $routeOptions,
        public array $wildcards
    ) {
        $this->path = $this->uri;
        $this->filterRouteOptions();
        $this->parseRoute();
    }

    public function parseRoute(): void
    {
        foreach ($this->wildcards as $key => $value) {
            $search = "{:$key}";
            if (str_contains($this->uri, $search))
                $this->uri = str_replace($search, $value, $this->uri);

            $search = "/{:?$key}";
            if (str_contains($this->uri, $search))
                $this->uri = str_replace($search, "/?($value)?", $this->uri);
        }

        if ($this->getOption("prefix"))
            $this->uri = "/" . trim($this->getOption("prefix"), "/") . $this->uri;

        if ($this->getOption("name"))
            $this->routeName = $this->getOption("name");
    }

    public function getRegex(): string
    {
        return '/^' . str_replace('/', '\/', rtrim($this->uri, "/")) . '$/';
    }

    public function getAction(string $defaultNamespace): array|\Closure
    {
        if (is_string($this->callback)) {
            [$controller, $method] = explode('@', $this->callback);
            $controller = $defaultNamespace . $controller;

            if (!class_exists($controller) || !method_exists($controller, $method))
                throw new \Exception( "O controller {$controller} ou método {$method} não foram encontrados!");

            return [
                new $controller,
                $method
            ];
        }

        return $this->callback;
    }

    private function filterRouteOptions(): void
    {
        $validOptions = ["parameters", "prefix", "name"];
        
        foreach ($this->routeOptions as $option => $value)
            if (!in_array($option, $validOptions))
                throw new \Exception("Erro opção {$option} não é valida!");
    }

    public function getOption(string $option): mixed
    {
        return $this->routeOptions[$option] ?? null;
    }

    public function name(string $name): void
    {
        $this->routeName = $name;
    }

    public function getName(): string
    {
        return $this->routeName;
    }
}
