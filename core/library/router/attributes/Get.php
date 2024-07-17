<?php

namespace core\library\router\attributes;

use core\interfaces\IRouteAttribute;
use core\library\router\RouteOptions;
use core\library\router\Router;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Get implements IRouteAttribute
{
    public function __construct(
        private string $uri,
        private array|RouteOptions $routeOptions = [],
    ) {
    }

    public function setRoute(array $action): void
    {
        Router::get($this->uri, $action, $this->routeOptions);
    }

}
