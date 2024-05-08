<?php

namespace app\controllers;

class HomeController
{
    public function index(int $page = 1)
    {
        $arr = [
            "firstName" => "Josival",
            "lastName" => "Junior",
            "email" => "josival1998@gmail.com",
            "age" => 26,
            "height" => 1.73,
            "pass" => "123",
            "confirm_pass" => "123"
        ];
        $validate = validate();

        dump($validate->fromArray($arr, [
            "firstName" => "required|alpha",
            "lastName" => "required|alpha",
            "email" => "required|email",
            "age" => "required|int|between:18,50",
            "height" => "required|float",
            "pass" => "required|alphanum",
            "confirm_pass"=> "same:pass"
        ]), $validate->getMessages());
    }

}