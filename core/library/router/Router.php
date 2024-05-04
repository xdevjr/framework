<?php

namespace core\library\router;

class Router
{
    private static array $routes = [];
    private ?array $params = null;
    private array $routeOptions = [];
    private RouteWildcard $wildcards;

    public function __construct(
        private string $defaultNamespace
    ) {
        $this->wildcards = new RouteWildcard();
    }

    private function getCurrentUri(): string
    {
        return $_SERVER['REQUEST_URI'] !== "/" ? rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/") : "/";
    }

    private function getCurrentRequestMethod(): string
    {
        return strtolower($_POST["_method"] ?? $_SERVER['REQUEST_METHOD']);
    }

    public static function getUrl(string $name, array $parameters = []): ?string
    {
        $routeFound = null;
        foreach (self::$routes as $routes) {
            foreach ($routes as $route) {
                if ($route->getName() === $name)
                    $routeFound = $route;
            }
        }

        if ($routeFound) {
            $explodePath = explode('/', $routeFound->path);
            $explodeUri = explode('/', $routeFound->uri);

            $diff = array_diff($explodeUri, $explodePath);
            if (count($diff) == count($parameters)) {
                for ($i = 0; $i < count($parameters); $i++) {
                    if (preg_match('/' . ltrim($explodeUri[array_keys($diff)[$i]], '?') . '/', $parameters[$i]))
                        $explodeUri[array_keys($diff)[$i]] = $parameters[$i];
                    else
                        throw new \Exception('O tipo do parâmetro não corresponde ao necessário!');
                }
            } else {
                throw new \Exception('A quantidade de parâmetros deve ser: ' . count($diff) . ', você passou: ' . count($parameters) . '!');
            }

            $url = implode('/', $explodeUri);
            return $url;
        }
    }

    public function group(array $groupOptions, \Closure $callback)
    {
        $this->routeOptions = $groupOptions;
        call_user_func($callback, $this);
        $this->routeOptions = [];
    }

    public function match(string|array $methods, string $uri, \Closure|string $callback, array $routeOptions = []): ?Route
    {
        foreach ($routeOptions as $key => $value) {
            if (!in_array($key, array_keys($this->routeOptions))) {
                $this->routeOptions[$key] = $value;
            }
        }

        if (is_string($methods)) {
            return self::$routes[strtolower($methods)][] = new Route($uri, $callback, $this->routeOptions, $this->wildcards->get());
        } else if (is_array($methods)) {
            foreach ($methods as $method) {
                self::$routes[strtolower($method)][] = new Route($uri, $callback, $this->routeOptions, $this->wildcards->get());
            }
        }
        return null;
    }

    private function find(): Route|bool
    {
        foreach (self::$routes[$this->getCurrentRequestMethod()] as $route) {
            if (preg_match($route->getRegex(), $this->getCurrentUri())) {
                $explodeRoute = explode('/', ltrim(str_replace('(', '', $route->uri), '/'));
                $explodeCurrentUri = explode('/', ltrim($this->getCurrentUri(), '/'));
                $this->params = array_filter(array_diff($explodeCurrentUri, $explodeRoute));

                return $route;
            }
        }

        return false;
    }

    private function execute(Route $route): void
    {
        if ($route->getOption('parameters')) {
            call_user_func($route->getAction($this->defaultNamespace), ...[...$route->getOption('parameters'), ...$this->params]);
            return;
        }

        call_user_func($route->getAction($this->defaultNamespace), ...$this->params);
    }

    public function start(): void
    {
        if (!$this->find())
            throw new \Exception("Rota não encontrada!");

        $this->execute($this->find());

        dump($this, [...self::$routes, "currentUri" => $this->getCurrentUri()]);
    }

    public function addWildcards(array $wildcards): void
    {
        foreach ($wildcards as $key => $value)
            $this->wildcards->add($key, $value);
    }

    public function get(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return $this->match('get', $uri, $callback, $routeOptions);
    }

    public function post(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return $this->match('post', $uri, $callback, $routeOptions);
    }

    public function put(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return $this->match('put', $uri, $callback, $routeOptions);
    }

    public function patch(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return $this->match('patch', $uri, $callback, $routeOptions);
    }

    public function delete(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return $this->match('delete', $uri, $callback, $routeOptions);
    }
}
