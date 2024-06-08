<?php

namespace app\controllers;

use core\library\database\Connection;
use core\library\database\query\QB;
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

        dump($user->model()->all()->paginate($links, 3, $page, url("home", [null]))->relationWith(Post::class, "user_id", alias: "posts")->result());
        echo $links->generateLinks(false);



        // $query = QB::create("users", Connection::get());
        // dump($query->select()->paginate($paginator, 3, $page, "/")->fetchAll());
        // echo $paginator->generateLinks(false);
    }

}