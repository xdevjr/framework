<?php

namespace app\middlewares;

use core\interfaces\MiddlewareInterface;

class Teste implements MiddlewareInterface
{
    public function execute(){
        dump("teste middleware");
    }
}
