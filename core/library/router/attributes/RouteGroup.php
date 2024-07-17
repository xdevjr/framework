<?php

namespace core\library\router\attributes;

use core\library\router\RouteOptions;
use core\library\router\Router;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RouteGroup
{
    public function __construct(
        private array|RouteOptions $groupOptions
    ) {
    }

    public function setGroupRoutes(\Closure $callback): void
    {
        Router::group($this->groupOptions, $callback);
    }

}
