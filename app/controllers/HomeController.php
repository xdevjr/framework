<?php

namespace app\controllers;

use app\database\models\User;
use core\library\Request;
use app\database\models\Post;

class HomeController
{
    public function __construct(
        private Request $request
    ) {
    }

    public function index(int $page = 1)
    {
        $user = new User;

        $users = $user->all()->paginate($links, 3, $page, url("web.home", [null]))->relationWith(Post::class, "user_id", alias: "posts")->result();

        return view("home", [
            "users" => $users,
            "paginate" => $links->bootstrap(),
        ]);
    }

}