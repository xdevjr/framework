<?php

namespace core\library\router;

use core\enums\Method;
use core\interfaces\IClassLoader;
use core\interfaces\IRouteAttribute;

abstract class Router
{
    private static array $routes = [];
    private static ?array $params = null;
    /** @var RouteOptions[] $groupOptions */
    private static array $groupOptions = [];
    private static IClassLoader $customClassLoader;

    public static function setCustomClassLoader(IClassLoader $customClassLoader): void
    {
        self::$customClassLoader = $customClassLoader;
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
        $routeFound = array_reduce(self::$routes, function ($carry, $route) use ($name) {
            if ($route->getName() === $name)
                $carry = $route;
            return $carry;
        });

        if (array_is_list($parameters) and !empty($parameters))
            throw new \Exception("The parameters must be an associative array!");

        if (array_is_list($getParameters) and !empty($getParameters))
            throw new \Exception("The getParameters must be an associative array!");

        $getParametersString = $getParameters ? "?" . http_build_query($getParameters) : "";

        if ($routeFound) {
            $path = $routeFound->getPath();
            $params = $routeFound->getParametersNames();
            if (count($params) == count($parameters)) {
                foreach ($params as $name => $regex) {
                    if (preg_match("/{$regex}/", $parameters[$name] ?? ""))
                        $path[$name] = $parameters[$name];
                    else
                        throw new \Exception('The parameter type does not match what is required!');
                }
            } else {
                throw new \Exception('The number of parameters must be: ' . count($params) . ', you passed: ' . count($parameters) . '!');
            }

            $url = implode('/', $path);
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

    /**
     * @param \core\enums\Method $method
     * @param string $uri
     * @param \Closure|string|array $callback string example: "controller@method", array example: ["controller", "method"], closure example: function() {...}
     * @param array|\core\library\router\RouteOptions $routeOptions
     * @return \core\library\router\Route
     */
    public static function map(Method $method, string $uri, \Closure|string|array $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        $routeOptions = is_array($routeOptions) ? RouteOptions::create(...$routeOptions) : $routeOptions;
        $routeOptions->merge(...self::$groupOptions ?? new RouteOptions);

        if (is_string($callback))
            $callback = explode('@', $callback);

        return self::$routes[] = new Route($method, $uri, $callback, $routeOptions);
    }

    private static function find(): Route
    {
        $routes = array_filter(self::$routes, fn($route) => preg_match($route->getRegex(), self::getCurrentUri()));

        if (!$routes)
            throw new \Exception("Route not found!", 404);

        $routes = array_filter($routes, fn($route) => $route->getMethod() === self::getCurrentRequestMethod());

        if (!$routes)
            throw new \Exception("Method not allowed for this route!", 405);

        $route = array_shift($routes);
        $path = $route->getPath();
        $explodeCurrentUri = explode('/', self::getCurrentUri());
        $params = array_filter(array_diff($explodeCurrentUri, $path));
        self::$params = $params ? array_combine(array_keys($route->getParametersNames()), $params) : [];

        return $route;
    }

    private static function execute(Route $route): void
    {
        $route->executeMiddlewares();
        $parameters = array_merge($route->getParameters(), self::$params);

        $classLoader = self::$customClassLoader ?? new ClassLoader();

        $action = $route->getAction();
        if (is_callable($action))
            $classLoader->loadClosure($action, $parameters);
        elseif (is_array($action)) {
            extract($action);
            $classLoader->loadClass($controller, $method, $parameters);
        }
    }

    /**
     * Scan the directory and set the routes using the Router attributes
     * @param string $dir Path to the directory containing the classes using the Router attributes
     * @param string $namespace Namespace of the classes
     * @return void
     */
    public static function setAttributeRoutes(string $dir, string $namespace = 'app\controllers'): void
    {
        $dir = realpath($dir);
        if (!is_dir($dir))
            throw new \Exception("The directory does not exist!");

        $namespace = str_ends_with($namespace, "\\") ? $namespace : $namespace . "\\";

        $controllers = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        foreach ($controllers as $controller) {
            $file = $controller->getFilename();
            $className = $namespace . str_replace(".php", "", $file);
            $class = new \ReflectionClass($className);
            $methods = $class->getMethods();
            foreach ($methods as $method) {
                $attr = $method->getAttributes(IRouteAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
                if ($attr) {
                    foreach ($attr as $a) {
                        $a->newInstance()->setRoute([$class->getName(), $method->getName()]);
                    }
                }
            }
        }
    }

    /**
     * Starts the execution of the application by finding the appropriate route and executing it.
     *
     * @param \Closure|null $errors A closure that will be called if an exception occurs during execution. Receives the exception as a parameter.
     * @throws \Exception If a route is not found or if the request method is not supported.
     * @return void
     */
    public static function start(?\Closure $errors = null): void
    {
        try {
            $route = self::find();
            self::execute($route);
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

    /**
     * @param string $uri
     * @param \Closure|string|array $callback string example: "controller@method", array example: ["controller", "method"], closure example: function() {...}
     * @param array|\core\library\router\RouteOptions $routeOptions
     * @return \core\library\router\Route
     */
    public static function get(string $uri, \Closure|string|array $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::GET, $uri, $callback, $routeOptions);
    }

    /**
     * @param string $uri
     * @param \Closure|string|array $callback string example: "controller@method", array example: ["controller", "method"], closure example: function() {...}
     * @param array|\core\library\router\RouteOptions $routeOptions
     * @return \core\library\router\Route
     */
    public static function post(string $uri, \Closure|string|array $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::POST, $uri, $callback, $routeOptions);
    }

    /**
     * @param string $uri
     * @param \Closure|string|array $callback string example: "controller@method", array example: ["controller", "method"], closure example: function() {...}
     * @param array|\core\library\router\RouteOptions $routeOptions
     * @return \core\library\router\Route
     */
    public static function put(string $uri, \Closure|string|array $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::PUT, $uri, $callback, $routeOptions);
    }

    /**
     * @param string $uri
     * @param \Closure|string|array $callback string example: "controller@method", array example: ["controller", "method"], closure example: function() {...}
     * @param array|\core\library\router\RouteOptions $routeOptions
     * @return \core\library\router\Route
     */
    public static function patch(string $uri, \Closure|string|array $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::PATCH, $uri, $callback, $routeOptions);
    }

    /**
     * @param string $uri
     * @param \Closure|string|array $callback string example: "controller@method", array example: ["controller", "method"], closure example: function() {...}
     * @param array|\core\library\router\RouteOptions $routeOptions
     * @return \core\library\router\Route
     */
    public static function delete(string $uri, \Closure|string|array $callback, array|RouteOptions $routeOptions = new RouteOptions): Route
    {
        return self::map(Method::DELETE, $uri, $callback, $routeOptions);
    }
}
