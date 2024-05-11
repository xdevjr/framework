<?php

namespace app\controllers;

use app\database\entities\UserEntity;
use app\database\models\User;

class HomeController
{
    public function index(int $page = 1)
    {
        $user = new User;
        $entity = new UserEntity;
        $entity->password = "123";
        $entity->updated_at = null;

        dump($user->update($entity, 1));
    }

}