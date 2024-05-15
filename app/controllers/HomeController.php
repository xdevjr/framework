<?php

namespace app\controllers;

use Faker\Factory;
use app\database\models\Post;
use app\database\entities\PostEntity;
use app\database\entities\UserEntity;
use core\library\database\QueryBuilder;

class HomeController
{
    public function index(int $page = 1): void
    {
        $faker = Factory::create("pt_BR");
        $user = new UserEntity([
            "firstName" => $faker->firstName,
            "lastName" => $faker->lastName,
            "email" => $faker->email,
            "password" => $faker->password,
        ]);

        dump($user, $user->getModel()->all()->relationWith(Post::class, "user_id", alias: "posts")->getResult());


        // $query = new QueryBuilder;
        // dump($query->query("select * from users")->paginate(5, $page, "/")->fetchAll());
        // echo $query->paginateLinks;
    }

}