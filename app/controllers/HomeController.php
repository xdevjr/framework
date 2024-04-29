<?php

namespace app\controllers;

use core\library\Paginator;

class HomeController
{
    public function index(int $page = 1)
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];
        $paginator = paginator($page, 3, count($arr), '/');
        return view('home', [
            'title' => "Pagina $page",
            'arr' => array_slice($arr, $paginator->getOffset(), $paginator->getLimit()),
            'paginate' => $paginator->generateLinks()
        ]);
    }

}