<?php

namespace core\library\database;

#[\AllowDynamicProperties]
abstract class Entity
{
    protected static string $modelNamespace = "app\\database\\models\\";
    private ?DBLayer $model = null;

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
        $properties = get_object_vars($this);
        unset($properties["model"]);
        return $properties;
    }

    public function model(): DBLayer
    {
        if (!$this->model) {
            $model = self::$modelNamespace . str_replace("Entity", "", (new \ReflectionClass($this))->getShortName());
            $this->model = new $model($this);
        }

        return $this->model;
    }
}
