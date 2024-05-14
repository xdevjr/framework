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
        $user = new UserEntity;
        $user->set([
            "firstName" => $faker->firstName,
            "lastName" => $faker->lastName,
            "email" => $faker->email,
            "password" => $faker->password,
        ]);

        dump($user->getModel()->all()->relationWith(Post::class, "id", "user_id")->getResult());
        

        // $query = new QueryBuilder;
        // dump($query->query("select * from users")->paginate( 1, $page, "/")->fetch());
        // echo $query->paginateLinks;
    }

}