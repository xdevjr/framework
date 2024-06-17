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

    /**
     * Return model for this entity class based on class name, replaces $entitySuffix with $modelSuffix
     * 
     * @param string $entitySuffix suffix of entity class name to remove e.g. if set "Entity" and entity name is "UserEntity" will return "User"
     * @param string $modelSuffix suffix of model class name to append e.g. if set "Model" and entity name is "UserEntity" will return "UserModel"
     * @return \core\library\database\DBLayer
     */
    public function model(string $entitySuffix = "Entity", string $modelSuffix = ""): DBLayer
    {
        if (!$this->model) {
            $model = self::$modelNamespace . str_replace($entitySuffix, $modelSuffix, (new \ReflectionClass($this))->getShortName());
            $this->model = (new $model())->setEntity($this);
        }

        return $this->model;
    }
}
