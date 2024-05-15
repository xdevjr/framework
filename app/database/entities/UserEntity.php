<?php

namespace app\database\entities;

use core\library\database\Entity;

class UserEntity extends Entity
{
    public function __construct(array $properties = [])
    {
        $validate = validate();
        $valid = $validate->fromArray($properties, [
            "firstName" => "required|alpha",
            "lastName" => "required|alpha",
            "email" => "required|email",
            "password" => "required|alphanumspecial",
        ]);
        if (!$valid) {
            throw new \Exception($validate->getFirstMessage());
        }
        parent::__construct($properties);
        $this->passwordHash();
    }
    public function passwordHash(): void
    {
        if (isset($this->password))
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        if (isset($this->password))
            return password_verify($password, $this->password);
        else
            return false;
    }
}
