<?php

namespace app\library;

use core\interfaces\IClassLoader;
use core\library\container\Container;

class ClassLoaderDI implements IClassLoader
{
    public function __construct(
        private Container $container
    ) {
    }

    public function loadClass(string $className, string $method, array $parameters = []): void
    {
        $this->container->call($className, $method, $parameters);
    }

    public function loadClosure(callable $closure, array $parameters = []): void
    {
        call_user_func($closure, ...$parameters);
    }
}
