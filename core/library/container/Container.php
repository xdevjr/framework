<?php

namespace core\library\container;

class Container
{
    private array $bindings = [];

    /**
     * Bind value to container with the given key
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function bind(string $key, mixed $value): void
    {
        $this->bindings[$key] = $value;
    }

    /**
     * @param array|string $definitions array of definitions or absolute path to the file that returns array of definitions
     * @return void
     */
    public function addDefinitions(array|string $definitions): void
    {
        if (is_string($definitions) and file_exists($definitions))
            $definitions = require $definitions;

        if (!is_array($definitions))
            throw new \Exception("The definitions must be an array or a path to a file that returns an array!");

        foreach ($definitions as $key => $value)
            $this->bind($key, $value);
    }

    /**
     * Returns the value of the given key or null if not found
     * @param string $key key of container or class name to resolve
     * @return mixed
     */
    public function get(string $key): mixed
    {
        if (isset($this->bindings[$key])) {
            $bind = $this->bindings[$key];
            if (is_callable($bind))
                return $bind();

            return $bind;
        }

        if (class_exists($key))
            return $this->instance($key);

        return null;
    }

    /**
     * @param string $key key of container or class name to resolve
     * @param string $method method to call
     * @param array $parameters array of parameters that will be passed to the method appended to the resolved parameters
     * @return void
     */
    public function call(string $key, string $method, array $parameters = []): void
    {
        $class = $this->get($key);
        $method = new \ReflectionMethod($class, $method);
        $parameters = array_merge($this->parameters($method), $parameters);
        call_user_func([$class, $method->getName()], ...$parameters);
    }

    /**
     * Returns an instance of a class with parameters resolved or null if not found
     * @param string $className
     * @return object|null
     */
    private function instance(string $className): ?object
    {
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if (!$constructor)
            return $reflection->newInstance();

        $params = $this->parameters($constructor);
        return $reflection->newInstanceArgs($params);
    }

    /**
     * Returns an array of resolved parameters that will be passed to the method
     * @param \ReflectionMethod $method
     * @return array
     */
    private function parameters(\ReflectionMethod $method): array
    {
        return array_filter(array_map(function ($param) {
            return $this->get($param->getType()->getName());
        }, $method->getParameters()));
    }
}
