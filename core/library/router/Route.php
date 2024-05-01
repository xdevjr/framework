<?php

namespace core\library\router;

class Route
{
    private array $wildcards = [
        "num" => "[0-9]+",
        "alpha" => "[a-z]+",
        "any" => "[a-z0-9\-]+",
    ];

    public function __construct(
        public string $uri,
        public \Closure|string $callback,
        public array $routeOptions
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
                $this->uri = str_replace($search, "(/)?($value)?", $this->uri);
        }
    }

    public function getRegex(): string
    {
        return '/^' . str_replace('/', '\/', $this->uri) . '$/';
    }

    public function getControllerMethod(string $defaultNamespace): ?array
    {
        if (is_string($this->callback)) {
            $controllerMethod = explode('@', $this->callback);
            $controllerMethod[0] = $defaultNamespace . $controllerMethod[0]; 

            return [
                new $controllerMethod[0],
                $controllerMethod[1]
            ];
        }

        return null;
    }
}
