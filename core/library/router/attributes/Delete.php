<?php

namespace core\library\router\attributes;

use core\interfaces\IRouteAttribute;
use core\library\router\Router;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Delete implements IRouteAttribute
{
    public function __construct(
        private string $uri,
        private array $routeOptions = [],
    ) {
    }

    public function setRoute(array $action): void
    {
        Router::delete($this->uri, $action, $this->routeOptions);
    }

}
