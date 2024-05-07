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

    public static function getUrl(string $name, array $parameters = [], array $getParameters = []): string
    {
        $routeFound = null;
        foreach (self::$routes as $route) {
            if ($route->getName() === $name)
                $routeFound = $route;
        }

        $getParametersString = !empty($getParameters) ? "?" . http_build_query($getParameters) : "";
        $parametersString = !empty($parameters) ? "/" . implode("/", $parameters) : "";

        if ($routeFound) {
            $explodePath = explode('/', $routeFound->getPath());
            $explodeUri = explode('/', $routeFound->getUri());

            $diff = array_diff($explodeUri, $explodePath);
            if (count($diff) == count($parameters)) {
                for ($i = 0; $i < count($parameters); $i++) {
                    if (preg_match('/' . ltrim($explodeUri[array_keys($diff)[$i]], '?') . '/', $parameters[$i]))
                        $explodePath[array_keys($diff)[$i]] = $parameters[$i];
                    else
                        throw new \Exception('O tipo do parâmetro não corresponde ao necessário!');
                }
            } else {
                throw new \Exception('A quantidade de parâmetros deve ser: ' . count($diff) . ', você passou: ' . count($parameters) . '!');
            }

            $url = implode('/', $explodePath);
            return $url . $getParameters;
        }

        return $name . $parametersString . $getParametersString;
    }

    public function group(array $groupOptions, \Closure $callback)
    {
        $this->routeOptions = $groupOptions;
        call_user_func($callback, $this);
        $this->routeOptions = [];
    }

    public function match(string|array $methods, string $uri, \Closure|string $callback, array $routeOptions = []): ?Route
    {
        $this->routeOptions["defaultNamespace"] = $this->defaultNamespace;
        $this->routeOptions = array_merge($this->routeOptions, $routeOptions);

        if (is_string($methods)) {
            return self::$routes[] = new Route(strtolower($methods), $uri, $callback, $this->routeOptions, $this->wildcards->get());
        } else if (is_array($methods)) {
            foreach ($methods as $method) {
                self::$routes[] = new Route(strtolower($method), $uri, $callback, $this->routeOptions, $this->wildcards->get());
            }
        }
        return null;
    }

    private function find(): Route
    {
        foreach (self::$routes as $route) {
            if (preg_match($route->getRegex(), $this->getCurrentUri())) {
                if ($route->getMethod() === $this->getCurrentRequestMethod()) {
                    $explodeRoute = explode('/', ltrim(str_replace('(', '', $route->getUri()), '/'));
                    $explodeCurrentUri = explode('/', ltrim($this->getCurrentUri(), '/'));
                    $this->params = array_filter(array_diff($explodeCurrentUri, $explodeRoute));

                    return $route;
                }

                throw new \Exception("Método não disponível para essa rota!", 405);
            }
        }

        throw new \Exception("Rota não encontrada!", 404);
    }

    private function execute(Route $route): void
    {
        $route->executeMiddlewares();

        if ($route->getOption('parameters')) {
            call_user_func($route->getAction(), ...[...$route->getOption('parameters'), ...$this->params]);
            return;
        }

        call_user_func($route->getAction(), ...$this->params);
    }

    public function start(\Closure $errors = null): void
    {
        try {
            $this->execute($this->find());
        } catch (\Exception $e) {
            if ($errors)
                call_user_func($errors, $e);
            else
                echo $e->getMessage();
            exit;
        }

        dump(["params" => $this->params, ...self::$routes, "currentUri" => $this->getCurrentUri(), "currentRequestMethod" => $this->getCurrentRequestMethod()]);
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
