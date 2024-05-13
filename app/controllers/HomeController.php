<?php

namespace app\controllers;

use app\database\entities\UserEntity;
use app\database\models\User;
use core\library\database\QueryBuilder;
use Faker\Factory;

class HomeController
{
    public function index(int $page = 1): void
    {
        $faker = Factory::create("pt_BR");
        $userEntity = new UserEntity;
        $userEntity->set([
            "updated_at"=> date("Y-m-d H:i:s"),
        ]);

        $userModel = new User;

        $query = new QueryBuilder;
        dump($query->update('users', $userEntity->getProperties(), "id", "between", [15,20]));
    }

}