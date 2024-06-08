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
        $routeOptions["defaultNamespace"] = self::$defaultNamespace;
        $routeOptions = array_merge(self::$routeOptions, $routeOptions);
        $routeOptions = new RouteOptions(...$routeOptions);

        if (is_string($methods)) {
            return self::$routes[] = new Route(strtolower($methods), $uri, $callback, $routeOptions);
        } else if (is_array($methods)) {
            foreach ($methods as $method) {
                self::$routes[] = new Route(strtolower($method), $uri, $callback, $routeOptions);
            }
        }
        return null;
    }

    private static function find(): Route
    {
        $routes = array_filter(self::$routes, fn($route) => preg_match($route->getRegex(), self::getCurrentUri()));

        if (!$routes)
            throw new \Exception("Route not found!", 404);

        $routes = array_filter($routes, fn($route) => $route->getMethod() === self::getCurrentRequestMethod());

        if (!$routes)
            throw new \Exception("Method not allowed for this route!", 405);

        sort($routes);
        $route = $routes[0];
        $explodeRoute = explode('/', ltrim($route->getPath(), '/'));
        $explodeCurrentUri = explode('/', ltrim(self::getCurrentUri(), '/'));
        self::$params = array_filter(array_diff($explodeCurrentUri, $explodeRoute));

        return $route;
    }

    private static function execute(Route $route): void
    {
        $route->executeMiddlewares();
        $parameters = array_merge($route->getParameters(), self::$params);

        call_user_func($route->getAction(), ...$parameters);
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
                echo nl2br("<div style='background-color: #f00; border:1px solid #000; border-radius: 5px; padding: 0 20px; color: white; font-weight: bold; text-shadow: 1px 1px 1px rgba(0,0,0,.8); display: flex; align-items: center; justify-content: center; gap: 40px; flex-wrap: wrap;'><p>Erro: {$e->getMessage()} \nLine: {$e->getLine()} \nFile: {$e->getFile()}</p><p style='border-left: 2px solid #000; padding-left: 10px'>Trace:\n{$e->getTraceAsString()}</p></div>");
            exit;
        }

    }

    public static function debug(): void
    {
        dump(["params" => self::$params, "routes" => self::$routes, "currentUri" => self::getCurrentUri(), "currentRequestMethod" => self::getCurrentRequestMethod()]);
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
