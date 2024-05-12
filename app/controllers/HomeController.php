<?php

namespace app\controllers;

use app\database\entities\UserEntity;
use app\database\models\User;
use Faker\Factory;

class HomeController
{
    public function index(int $page = 1): void
    {
        $faker = Factory::create("pt_BR");
        $userEntity = new UserEntity;
        $userEntity->set([
            "password" => "123"
        ]);

        $userModel = new User;

        dump($userModel->update($userEntity, 4));
    }

}