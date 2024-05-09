<?php

namespace core\library\router;

use core\interfaces\MiddlewareInterface;

class Route
{
    private ?string $routeName = null;
    private string $path;

    public function __construct(
        private string $method,
        private string $uri,
        private \Closure|string $callback,
        private array $routeOptions,
        private array $wildcards
    ) {
        $this->path = $this->getOption("prefix") ? "/" . trim($this->getOption("prefix"), "/") . $this->uri : $this->uri;
        $this->filterRouteOptions();
        $this->parseRoute();
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    private function parseRoute(): void
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
            $this->routeName = $this->getOption("groupName") ? $this->getOption("groupName") . "." . $this->getOption("name") : $this->getOption("name");
    }

    public function getRegex(): string
    {
        return '/^' . str_replace('/', '\/', rtrim($this->getOption("customRegex") ?? $this->uri, "/")) . '$/';
    }

    public function getAction(): array|\Closure
    {
        if (is_string($this->callback)) {
            [$controller, $method] = explode('@', $this->callback);
            $controller = $this->getNamespace() . $controller;

            if (!class_exists($controller) || !method_exists($controller, $method))
                throw new \Exception("O controller {$controller} ou método {$method} não foram encontrados!", 501);

            return [
                new $controller,
                $method
            ];
        }

        return $this->callback;
    }

    private function filterRouteOptions(): void
    {
        $validOptions = ["parameters", "prefix", "name", "groupName", "middlewares", "namespace", "defaultNamespace", "customRegex"];

        foreach ($this->routeOptions as $option => $value)
            if (!in_array($option, $validOptions))
                throw new \Exception("Erro opção {$option} não é valida!");
    }

    public function executeMiddlewares(): void
    {
        if ($middlewares = $this->getOption("middlewares")) {
            foreach ($middlewares as $middleware) {
                if (!class_exists($middleware))
                    throw new \Exception("O middleware {$middleware} não foi encontrado!", 501);

                if (!new $middleware instanceof MiddlewareInterface)
                    throw new \Exception("O middleware {$middleware} deve implementar o " . MiddlewareInterface::class, 501);

                call_user_func([new $middleware, "execute"]);
            }
        }
    }

    public function getOption(string $option): mixed
    {
        return $this->routeOptions[$option] ?? null;
    }

    public function getName(): string
    {
        return $this->routeName;
    }

    private function getNamespace(): string
    {
        return $this->getOption("namespace") ?? $this->getOption("defaultNamespace");
    }

    public function name(string $name): static
    {
        $this->routeName = $this->getOption("groupName") ? $this->getOption("groupName") . "." . $name : $name;
        return $this;
    }

    public function middlewares(array $middlewares): static
    {
        $this->routeOptions["middlewares"] = $middlewares;
        return $this;
    }

    public function namespace(string $namespace): static
    {
        $this->routeOptions["namespace"] = $namespace;
        return $this;
    }

    public function customRegex(string $regex): static
    {
        $this->routeOptions["customRegex"] = $regex;
        return $this;
    }

}
