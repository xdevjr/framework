<?php

namespace core\library\router;

use core\interfaces\IClassLoader;

class ClassLoader implements IClassLoader
{
    private function instance(string $className): object
    {
        if (!class_exists($className))
            throw new \Exception("The class {$className} was not found!");

        return new $className();
    }
    public function loadClass(string $className, string $method, array $parameters = []): void
    {
        $class = $this->instance($className);
        call_user_func_array([$class, $method], $parameters);
    }

    public function loadClosure(callable $closure, array $parameters = []): void
    {
        call_user_func_array($closure, $parameters);
    }

}
