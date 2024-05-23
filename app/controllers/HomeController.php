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
        
        dump($user->getModel()->all()->relationWith(Post::class, "user_id", alias: "posts")->getResult());


        // $query = QueryBuilder::table("users");
        // dump($query->select()->rightJoin("posts", ["title", "content"])->on("id", "=","user_id")->where("id", ">", "5")->fetchAll());
        // echo $query->paginateLinks;
    }

}