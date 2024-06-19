<?php

namespace app\middlewares;

use core\interfaces\IMiddleware;

class CSRF implements IMiddleware
{
    public function execute(): void
    {
        csrfCreateAndCheck();
    }
}
