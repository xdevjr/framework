<?php

namespace core\library\router;

abstract class Router
{
    private static array $routes = [];
    private static ?array $params = null;
    private static array $routeOptions = [];
    private static string $defaultNamespace;

    public static function setDefaultNamespace(string $namespace): void
    {
        self::$defaultNamespace = $namespace;
    }

    private static function getCurrentUri(): string
    {
        return $_SERVER['REQUEST_URI'] !== "/" ? rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/") : "/";
    }

    private static function getCurrentRequestMethod(): string
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
                    if (preg_match('/' . ltrim($explodeUri[array_keys($diff)[$i]], '?') . '/', $parameters[$i] ?? ""))
                        $explodePath[array_keys($diff)[$i]] = $parameters[$i];
                    else
                        throw new \Exception('The parameter type does not match what is required!');
                }
            } else {
                throw new \Exception('The number of parameters must be: ' . count($diff) . ', you passed: ' . count($parameters) . '!');
            }

            $url = implode('/', $explodePath);
            return $url . $getParametersString;
        }

        return $name . $parametersString . $getParametersString;
    }

    public static function group(array $groupOptions, \Closure $callback)
    {
        self::$routeOptions = $groupOptions;
        call_user_func($callback);
        self::$routeOptions = [];
    }

    public static function match(string|array $methods, string $uri, \Closure|string $callback, array $routeOptions = []): ?Route
    {
        self::$routeOptions["defaultNamespace"] = self::$defaultNamespace;
        self::$routeOptions = array_merge(self::$routeOptions, $routeOptions);

        if (is_string($methods)) {
            return self::$routes[] = new Route(strtolower($methods), $uri, $callback, self::$routeOptions);
        } else if (is_array($methods)) {
            foreach ($methods as $method) {
                self::$routes[] = new Route(strtolower($method), $uri, $callback, self::$routeOptions);
            }
        }
        return null;
    }

    private static function find(): Route
    {
        foreach (self::$routes as $route) {
            if (preg_match($route->getRegex(), self::getCurrentUri())) {
                if ($route->getMethod() === self::getCurrentRequestMethod()) {
                    $explodeRoute = explode('/', ltrim(str_replace('(', '', $route->getUri()), '/'));
                    $explodeCurrentUri = explode('/', ltrim(self::getCurrentUri(), '/'));
                    self::$params = array_filter(array_diff($explodeCurrentUri, $explodeRoute));

                    return $route;
                }
            }
        }

        throw new \Exception("Route not found!", 404);
    }

    private static function execute(Route $route): void
    {
        $route->executeMiddlewares();

        if ($route->getOption('parameters')) {
            call_user_func($route->getAction(), ...[...$route->getOption('parameters'), ...self::$params]);
            return;
        }

        call_user_func($route->getAction(), ...self::$params);
    }

    /**
     * Starts the execution of the application by finding the appropriate route and executing it.
     *
     * @param \Closure|null $errors A closure that will be called if an exception occurs during execution. Receives the exception as a parameter.
     * @throws \Exception If a route is not found or if the request method is not supported.
     * @return void
     */
    public static function start(\Closure $errors = null): void
    {
        try {
            self::execute(self::find());
        } catch (\Exception $e) {
            if ($errors)
                call_user_func($errors, $e);
            else
                echo nl2br("Trace:\n{$e->getTraceAsString()}\n\n Erro: {$e->getMessage()} \nLine: {$e->getLine()} \nFile: {$e->getFile()}");
            exit;
        }

        //dump(["params" => $this->params, ...self::$routes, "currentUri" => $this->getCurrentUri(), "currentRequestMethod" => $this->getCurrentRequestMethod()]);
    }

    public static function addWildcards(array $wildcards): void
    {
        foreach ($wildcards as $key => $value)
            RouteWildcard::add($key, $value);
    }

    public static function get(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return self::match('get', $uri, $callback, $routeOptions);
    }

    public static function post(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return self::match('post', $uri, $callback, $routeOptions);
    }

    public static function put(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return self::match('put', $uri, $callback, $routeOptions);
    }

    public static function patch(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return self::match('patch', $uri, $callback, $routeOptions);
    }

    public static function delete(string $uri, \Closure|string $callback, array $routeOptions = []): Route
    {
        return self::match('delete', $uri, $callback, $routeOptions);
    }
}
