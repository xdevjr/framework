<?php

namespace core\library\router;

use core\enums\Method;

abstract class Router
{
    private static array $routes = [];
    private static ?array $params = null;
    /** @var RouteOptions[] $groupOptions */
    private static array $groupOptions = [];
    private static string $defaultNamespace;

    public static function setDefaultNamespace(string $namespace): void
    {
        self::$defaultNamespace = $namespace;
    }

    private static function getCurrentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = $uri !== "/" ? rtrim($uri, "/") : "/";
        return $uri;
    }

    private static function getCurrentRequestMethod(): string
    {
        return strtoupper($_REQUEST["_method"] ?? $_SERVER['REQUEST_METHOD']);
    }

    public static function getUri(string $name, array $parameters = [], array $getParameters = []): string
    {
        $routeFound = array_values(array_filter(self::$routes, fn($route) => $route->getName() === $name))[0] ?? null;

        if (!array_is_list($parameters) and !empty($parameters))
            throw new \Exception("The parameters cannot be an associative array!");

        if (array_is_list($getParameters) and !empty($getParameters))
            throw new \Exception("The getParameters must be an associative array!");

        $getParametersString = $getParameters ? "?" . http_build_query($getParameters) : "";

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

        return $name . $getParametersString;
    }

    public static function group(array|RouteOptions $groupOptions, \Closure $callback): void
    {
        self::$groupOptions[] = is_array($groupOptions) ? RouteOptions::create(...$groupOptions)->clearNonGroupOptions() : $groupOptions->clearNonGroupOptions();
        call_user_func($callback);
        array_pop(self::$groupOptions);
    }

    public static function map(Method $methods, string $uri, \Closure|string $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        $routeOptions = is_array($routeOptions) ? RouteOptions::create(...$routeOptions) : $routeOptions;
        $routeOptions->setOption("defaultNamespace", self::$defaultNamespace);
        $routeOptions->merge(...self::$groupOptions ?? new RouteOptions);

        return self::$routes[] = new Route($methods, $uri, $callback, $routeOptions);
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
                echo nl2br("<div style='background-color: #f00; border:1px solid #000; border-radius: 5px; color: white; font-weight: bold; text-shadow: 1px 1px 1px rgba(0,0,0,.8); display: flex; flex-direction: column; box-sizing: border-box; padding: 0 20px;'><p style='box-sizing: border-box; width: 100%; border-bottom: 1px solid #000; padding-bottom: 20px'>Erro: {$e->getMessage()} \nLine: {$e->getLine()} \nFile: {$e->getFile()}</p><p style='box-sizing: border-box; width: 100%;'>Trace:\n{$e->getTraceAsString()}</p></div>");
            exit;
        }

    }

    public static function debug(): void
    {
        dump([
            "params" => self::$params,
            "routes" => self::$routes,
            "currentUri" => self::getCurrentUri(),
            "currentRequestMethod" => self::getCurrentRequestMethod(),
            "groupOptions" => self::$groupOptions
        ]);
    }

    public static function addWildcards(array $wildcards): void
    {
        if (array_is_list($wildcards))
            throw new \Exception("The wildcards must be an associative array!");

        foreach ($wildcards as $key => $value)
            RouteWildcard::add($key, $value);
    }

    public static function get(string $uri, \Closure|string $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::GET, $uri, $callback, $routeOptions);
    }

    public static function post(string $uri, \Closure|string $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::POST, $uri, $callback, $routeOptions);
    }

    public static function put(string $uri, \Closure|string $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::PUT, $uri, $callback, $routeOptions);
    }

    public static function patch(string $uri, \Closure|string $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::PATCH, $uri, $callback, $routeOptions);
    }

    public static function delete(string $uri, \Closure|string $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::DELETE, $uri, $callback, $routeOptions);
    }
}
