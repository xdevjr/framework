<?php

namespace app\database\entities;

use core\library\database\Entity;

class UserEntity extends Entity
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $password;
    public string $created_at;
    public ?string $updated_at;
}
