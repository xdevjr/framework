<?php

namespace app\controllers;

use app\database\models\User;
use core\library\Request;
use app\database\models\Post;
use core\library\Response;
use core\library\router\attributes\Get;


class HomeController
{
    public function __construct(
        private Request $request
    ) {
    }

    #[Get("/{page:?num}", ["name" => "home"])]
    public function index(int $page = 1): Response
    {
        $user = new User;

        $users = $user->all()->paginate($links, 3, $page, url("home", ["page" => null]))->relationWith(Post::class, "user_id", alias: "posts")->result();

        return view("home", [
            "users" => $users,
            "paginate" => $links->bootstrap(),
        ]);
    }

}