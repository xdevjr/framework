<?php

namespace core\interfaces;

interface IClassLoader
{
    public function loadClass(string $className, string $method, array $parameters = []): void;

    public function loadClosure(callable $closure, array $parameters = []): void;
}
