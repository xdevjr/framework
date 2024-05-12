<?php

namespace app\controllers;

use app\database\entities\UserEntity;
use app\database\models\User;

class HomeController
{
    public function index(int $page = 1): void
    {
        $user = new User;

        dump($user->all());
    }

}