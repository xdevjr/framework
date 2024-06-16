<?php

namespace app\database\entities;

use core\library\database\Entity;

class UserEntity extends Entity
{
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        $this->validate();
    }
    private function validate(): void
    {
        $validate = validate($this->getProperties(), [
            "firstName" => "alpha",
            "lastName" => "alpha",
            "email" => "email",
            "password" => "alphanumspecial",
        ]);

        if (!$validate->isValid())
            throw new \Exception($validate->getFirstMessage());
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
