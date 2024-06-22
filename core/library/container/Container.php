<?php

namespace core\library\container;

class Container
{
    private array $bindings = [];
    
    public function bind(string $key, mixed $value): void
    {
        $this->bindings[$key] = $value;
    }

    public function addDefinitions(array $definitions): void
    {
        foreach ($definitions as $key => $value) {
            $this->bind($key, $value);
        }
    }

    public function get(string $key): mixed
    {
        if (isset($this->bindings[$key])) {
            $bind = $this->bindings[$key];
            if (is_callable($bind))
                return $bind();

            return $bind;
        }

        if (class_exists($key))
            return $this->getInstance($key);

        return null;
    }

    public function call(string $key, string $method, array $parameters = []): void
    {
        $class = $this->get($key);
        $method = new \ReflectionMethod($class, $method);
        $parameters = array_merge($this->resolveParameters($method), $parameters);
        call_user_func([$class, $method->getName()], ...$parameters);
    }

    private function getInstance(string $key): mixed
    {
        $reflection = new \ReflectionClass($key);
        $constructor = $reflection->getConstructor();

        if (!$constructor)
            return $reflection->newInstance();

        $params = $this->resolveParameters($constructor);
        return $reflection->newInstanceArgs($params);
    }

    private function resolveParameters(\ReflectionMethod $method): array
    {
        return array_filter(array_map(function ($param) {
            return $this->get($param->getType()->getName());
        }, $method->getParameters()));
    }
}
