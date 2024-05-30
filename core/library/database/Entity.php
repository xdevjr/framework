<?php

namespace core\library\database;

#[\AllowDynamicProperties]
abstract class Entity
{
    protected static string $modelNamespace = "app\\database\\models\\";

    public static function setModelNamespace(string $namespace): void
    {
        self::$modelNamespace = $namespace;
    }

    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }

    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    public function getProperties(): array
    {
        return get_object_vars($this);
    }

    public function getModel(): DBLayer
    {
        $model = self::$modelNamespace . str_replace("Entity", "", (new \ReflectionClass(static::class))->getShortName());
        return new $model($this);
    }
}
