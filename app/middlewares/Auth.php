<?php

namespace app\middlewares;

use core\interfaces\MiddlewareInterface;

class Auth implements MiddlewareInterface
{
    public function execute()
    {
        dump("auth middleware");
    }
}
