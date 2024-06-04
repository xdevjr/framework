<?php

namespace app\middlewares;

use core\interfaces\IMiddleware;

class Teste implements IMiddleware
{
    public function execute()
    {
        dump("teste middleware");
    }
}
