<?php

namespace app\middlewares;

use core\interfaces\IMiddleware;

class Auth implements IMiddleware
{
    public function execute(): void
    {
        dump("auth middleware");
    }
}
