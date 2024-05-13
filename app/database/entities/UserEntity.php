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

    public function passwordHash(): void
    {
        if (isset($this->password)) 
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password)
    {
        return password_verify($password, $this->password);
    }

    public function set(array $properties): void
    {
        dump($properties);
        $validate = validate();
        $valid = $validate->fromArray($properties, [
            "firstName"=> "required|alpha",
            "lastName"=> "required|alpha",
            "email"=> "required|email",
            "password"=> "required|alphanumspecial",
        ]);
        if (!$valid) {
            throw new \Exception($validate->getFirstMessage());
        }
        parent::set($properties);
        $this->passwordHash();
    }
}
