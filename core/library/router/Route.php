<?php

namespace core\library\router;

use core\interfaces\IMiddleware;
use core\enums\Method;

class Route
{
    private array $path;

    public function __construct(
        private Method $method,
        private string $uri,
        private \Closure|array $callback,
        private RouteOptions $routeOptions
    ) {
        $this->uri = $this->routeOptions->getOption("prefix") . "/" . ltrim($this->uri, "/");
        $this->setPath($this->uri);
        $this->parseRoute();
    }

    private function setPath(string $path): void
    {
        $path = rtrim($path, "/") ?: "/";
        $path = explode("/", $path);
        $path = array_reduce($path, function ($carry, $item) {
            if (preg_match("/\{.+?\}/", $item)) {
                [$name, $wildcard] = explode(":", trim($item, "{}"));
                $carry[$name] = ltrim(RouteWildcard::get($wildcard), "?");
            } else {
                $carry[] = $item;
            }

            return $carry;
        }, []);
        $this->path = $path;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method->name;
    }

    private function parseRoute(): void
    {
        $search = array_map(fn($key) => "/\{[A-z\_]+\:$key\}/", array_keys(RouteWildcard::get()));
        $searchOptional = array_map(fn($key) => "/\{[A-z\_]+\:\?$key\}/", array_keys(RouteWildcard::get()));
        $search = array_merge($search, $searchOptional);
        $replace = array_values(RouteWildcard::get());
        $replaceOptional = array_map(fn($key) => "?($key)?", $replace);
        $replace = array_merge($replace, $replaceOptional);

        $this->uri = preg_replace($search, $replace, $this->uri);
    }

    public function getParametersNames(): ?array
    {
        return array_filter($this->path, fn($item) => is_string($item), ARRAY_FILTER_USE_KEY) ?? null;
    }

    public function getRegex(): string
    {
        $uri = rtrim($this->uri, "/") ?: "/";
        $uri = $this->routeOptions->getOption("customRegex") ?: str_replace('/', '\/', $uri);
        return '/^' . $uri . '$/';
    }

    public function getAction(): array|\Closure
    {
        if (!is_callable($this->callback)) {
            [$controller, $method] = $this->callback;
            $controller = $this->getNamespace() . $controller;

            if (!class_exists($controller) || !method_exists($controller, $method))
                throw new \Exception("Controller {$controller} or method {$method} were not found!", 501);

            return [
                "controller" => $controller,
                "method" => $method
            ];
        }

        return $this->callback;
    }

    public function executeMiddlewares(): void
    {
        if ($middlewares = $this->routeOptions->getOption("middlewares")) {
            foreach ($middlewares as $middleware) {
                if (!class_exists($middleware))
                    throw new \Exception("The middleware {$middleware} was not found!", 501);

                $middleware = new $middleware;
                if (!$middleware instanceof IMiddleware)
                    throw new \Exception("The {$middleware} middleware needs to implement the " . IMiddleware::class, 501);

                call_user_func([$middleware, "execute"]);
            }
        }
    }

    public function getNamespace(): string
    {
        return $this->routeOptions->getOption("namespace");
    }

    public function getName(): string
    {
        return empty($this->routeOptions->getOption("groupName")) ? $this->routeOptions->getOption("name") : $this->routeOptions->getOption("groupName") . "." . $this->routeOptions->getOption("name");
    }

    public function getParameters(): array
    {
        return $this->routeOptions->getOption("parameters");
    }

    public function name(string $name): static
    {
        $this->routeOptions->setOption("name", $name);
        return $this;
    }

    public function overrideMiddlewares(string ...$middlewares): static
    {
        $this->routeOptions->setOption("middlewares", $middlewares);
        return $this;
    }

    public function middlewares(string ...$middlewares): static
    {
        $middlewares = array_merge($this->routeOptions->getOption("middlewares"), $middlewares);
        $this->routeOptions->setOption("middlewares", $middlewares);
        return $this;
    }

    public function namespace(string $namespace): static
    {
        $this->routeOptions->setOption("namespace", $namespace);
        return $this;
    }

    public function customRegex(string $regex): static
    {
        $this->routeOptions->setOption("customRegex", $regex);
        return $this;
    }

    public function parameters(array $parameters): static
    {
        $this->routeOptions->setOption("parameters", $parameters);
        return $this;
    }

}
