<?php

namespace app\controllers;

use core\library\Paginator;
use core\library\Request;

class HomeController
{
    public function index(?int $page = null)
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40];

        $paginator = new Paginator($page ?? 1, 5, count($arr), 10, '/');

        dump(array_slice($arr, $paginator->getOffset(), $paginator->getLimit()));

        echo $paginator->generateLinks();
    }

}