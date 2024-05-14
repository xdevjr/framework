<?php

namespace core\library\database;

#[\AllowDynamicProperties]
abstract class Entity
{

    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    public function getProperties(): array
    {
       return get_object_vars($this);
    }

    public function set(array $properties): void
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }

    public function getModel(): DBLayer
    {
        $model = MODEL_NAMESPACE.str_replace("Entity", "", (new \ReflectionClass(static::class))->getShortName());
        return (new $model)->entity($this);
    }
}
