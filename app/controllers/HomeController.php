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
            "firstName" => $faker->firstName,
            "lastName" => $faker->lastName,
            "email" => $faker->email,
            "password" => $faker->password,
        ]);

        $userModel = new User;
        // dump($userModel->save($userEntity));

        $query = new QueryBuilder;
        dump($query->query("select * from users")->paginate( 1, $page, "/")->fetch());
        echo $query->paginateLinks;
    }

}