<?php

namespace core\library\router;

class Router
{
    public array $routes = [];
    public ?array $params = null;

    public function __construct(
        public string $defaultNamespace
    )
    {
    }

    private function getCurrentUri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    private function getCurrentRequestMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function match(string|array $methods, string $uri, \Closure|string $callback, array $routeOptions = [])
    {
        if (is_string($methods)) {
            $this->routes[$methods][] = new Route($uri, $callback, $routeOptions);
        } else if (is_array($methods)) {
            foreach ($methods as $method) {
                $this->routes[$method][] = new Route($uri, $callback, $routeOptions);
            }
        }
    }

    private function find()
    {
        foreach ($this->routes[$this->getCurrentRequestMethod()] as $route) {
            if (preg_match($route->getRegex(), $this->getCurrentUri(), $match)) {
                $explodeRoute = explode('/', ltrim(str_replace('(', '', $route->uri), '/'));
                $explodeCurrentUri = explode('/', ltrim($this->getCurrentUri(), '/'));
                $this->params = array_filter(array_diff($explodeCurrentUri, $explodeRoute));

                return $route;
            }
        }

        return false;
    }

    private function execute(Route $route)
    {
        call_user_func($route->getControllerMethod($this->defaultNamespace), ...$this->params);
    }

    public function start()
    {
        if ($this->find())
            $this->execute($this->find());
    }
}
