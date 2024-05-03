<?php

namespace core\library\router;

class Router
{
    private array $routes = [];
    private ?array $params = null;
    private RouteWildcard $wildcards;

    public function __construct(
        private string $defaultNamespace
    ) {
        $this->wildcards = new RouteWildcard();
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
            return $this->routes[strtolower($methods)][] = new Route($uri, $callback, $routeOptions, $this->wildcards->get());
        } else if (is_array($methods)) {
            foreach ($methods as $method) {
                $this->routes[strtolower($method)][] = new Route($uri, $callback, $routeOptions, $this->wildcards->get());
            }
        }
        return;
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
        if ($route->getOption('parameters')) {
            call_user_func($route->getAction($this->defaultNamespace), ...[...$route->getOption('parameters'), ...$this->params]);
            return;
        }

        call_user_func($route->getAction($this->defaultNamespace), ...$this->params);
    }

    public function start()
    {
        if ($this->find())
            $this->execute($this->find());

        dump($this);
    }

    public function addWildcards(array $wildcards)
    {
        foreach ($wildcards as $key => $value)
            $this->wildcards->add($key, $value);
    }

    public function get(string $uri, \Closure|string $callback, array $routeOptions = [])
    {
        return $this->match('get', $uri, $callback, $routeOptions);
    }

    public function post(string $uri, \Closure|string $callback, array $routeOptions = [])
    {
        return $this->match('post', $uri, $callback, $routeOptions);
    }

    public function put(string $uri, \Closure|string $callback, array $routeOptions = [])
    {
        return $this->match('put', $uri, $callback, $routeOptions);
    }

    public function patch(string $uri, \Closure|string $callback, array $routeOptions = [])
    {
        return $this->match('patch', $uri, $callback, $routeOptions);
    }

    public function delete(string $uri, \Closure|string $callback, array $routeOptions = [])
    {
        return $this->match('delete', $uri, $callback, $routeOptions);
    }
}
