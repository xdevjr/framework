<?php

namespace core\library\router;

class Route
{
    private ?string $routeName = null;

    public function __construct(
        public string $uri,
        public \Closure|string $callback,
        public array $routeOptions,
        public array $wildcards
    ) {
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
            $this->uri = "/" . trim($this->getOption("prefix"), "/") . "/?" . $this->uri;

        if ($this->getOption("name"))
            $this->routeName = $this->getOption("name");
    }

    public function getRegex(): string
    {
        return '/^' . str_replace('/', '\/', $this->uri) . '$/';
    }

    public function getAction(string $defaultNamespace): array|\Closure
    {
        if (is_string($this->callback)) {
            $controllerMethod = explode('@', $this->callback);
            $controllerMethod[0] = $defaultNamespace . $controllerMethod[0];

            return [
                new $controllerMethod[0],
                $controllerMethod[1]
            ];
        }

        return $this->callback;
    }

    public function getOption(string $option): mixed
    {
        return $this->routeOptions[$option] ?? null;
    }

    public function name(string $name): void
    {
        $this->routeName = $name;
    }
}
