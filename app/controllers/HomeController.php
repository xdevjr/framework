<?php

namespace app\controllers;

use core\library\Request;

class HomeController
{
    public function index()
    {
        dd(Request::all()->get);
    }

}