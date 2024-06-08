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
        // $user = new UserEntity([
        //     "firstName" => $faker->firstName,
        //     "lastName" => $faker->lastName,
        //     "email" => $faker->email,
        //     "password" => $faker->password,
        // ]);

        // dump($user->getModel()->all()->paginate($links, 3, $page, url("home", [null]))->relationWith(Post::class, "user_id", alias: "posts")->getResult());
        // echo $links->generateLinks(false);



        $query = QB::create("userss", Connection::get());
        dump($query->transaction(function (QB $query) use ($faker) {
            return $query->update([
                "firstName" => $faker->firstName,
                "lastName" => $faker->lastName,
                "email" => $faker->email,
                "password" => $faker->password,
            ])->where("id", "=", 40)->execute() && $query->update([
                    "firstName" => $faker->firstName,
                    "lastName" => $faker->lastName,
                    "email" => $faker->email,
                    "password" => $faker->password,
                ])->where("id", "=", 41)->execute();
        }));

        // dump($query->select()->paginate($paginator, 3, $page, "/")->fetchAll());
        // echo $paginator->generateLinks(false);
    }

}