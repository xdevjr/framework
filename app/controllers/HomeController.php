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
        
        dump($user->getModel()->all()->paginate($links, 3, $page, url("home", [null]))->relationWith(Post::class, "user_id", alias: "posts")->getResult());
        echo $links->generateLinks(false);



        // $query = QueryBuilder::table("users");
        // dump($query->select()->where("id", ">=", 5)->andWhere("id", "<=", 10)->fetchAll());
        // echo $query->paginateLinks;
    }

}