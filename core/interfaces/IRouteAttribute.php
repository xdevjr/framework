<?php

namespace core\interfaces;

interface IRouteAttribute
{
    public function __construct(string $uri, array $routeOptions = []);
    public function setRoute(array $action): void;
}
