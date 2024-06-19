<?php

namespace app\controllers;

use core\library\database\Connection;
use core\library\database\query\QB;
use core\library\Response;
use Faker\Factory;
use app\database\models\Post;
use app\database\entities\UserEntity;

class HomeController
{
    public function index(int $page = 1): Response
    {
        $faker = Factory::create("pt_BR");
        $user = new UserEntity([
            "firstName" => $faker->firstName,
            "lastName" => $faker->lastName,
            "email" => $faker->email,
            "password" => $faker->password,
        ]);
        
        $users = $user->model()->all()->paginate($links, 3, $page, url("web.home", [null]))->relationWith(Post::class, "user_id", alias: "posts")->result();


        // $queryBuilder = QB::create("users", Connection::get());
        // dump($queryBuilder->query("SELECT * FROM users")->fetchAll());
        // dump($queryBuilder->select()->paginate($paginator, 3, $page, "/")->fetchAll());
        // echo $paginator->generateLinks(false);

        return response(view("home", [
            "users" => $users,
            "paginate" => $links->bootstrap(),
        ]));
    }

}