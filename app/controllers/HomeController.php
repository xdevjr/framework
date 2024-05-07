<?php

namespace app\controllers;

use core\library\Validate;

class HomeController
{
    public function index(int $page = 1)
    {
        $arr = [
            "firstName" => "",
            "lastName" => "Junior",
            "email"=> "josival1998@gmail.com",
            "age"=> 26,
            "height" => 1.73
        ];
        $validate = new Validate([
            "firstName" => "required|alpha",
            "lastName"=> "required|alpha",
            "email"=> "required|email",
            "age"=> "required|int",
            "height"=> "required|float"
        ]);

        dump($validate->validate($arr), $validate->getMessages());
    }

}