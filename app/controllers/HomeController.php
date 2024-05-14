<?php

namespace app\controllers;

use app\database\entities\UserEntity;
use Faker\Factory;

class HomeController
{
    public function index(int $page = 1): void
    {
        $faker = Factory::create("pt_BR");
        $user = new UserEntity;
        $user->set([
            "firstName" => $faker->firstName,
            "lastName" => $faker->lastName,
            "email" => $faker->email,
            "password" => $faker->password,
        ]);

        dump($user->getModel()->update(10));

        

        // $query = new QueryBuilder;
        // dump($query->query("select * from users")->paginate( 1, $page, "/")->fetch());
        // echo $query->paginateLinks;
    }

}