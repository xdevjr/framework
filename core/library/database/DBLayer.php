<?php

namespace core\library\database;

abstract class DBLayer
{

    protected string $table;
    protected static string $entityNamespace = "app\\database\\entities\\";

    public static function setEntityNamespace(string $namespace): void
    {
        self::$entityNamespace = $namespace;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder;
    }

    private function getEntity()
    {
        $reflect = new \ReflectionClass(static::class);
        $entity = self::$entityNamespace . $reflect->getShortName() . "Entity";
        if (!class_exists($entity)) {
            throw new \Exception("Entity {$entity} does not exist!");
        } elseif (!new $entity instanceof Entity) {
            throw new \Exception("Entity {$entity} needs to implement the " . Entity::class . "!");
        }

        return $entity;
    }
    public function all(string|array $fields = "*"): ?array
    {
        $result = $this->getQueryBuilder()->select($this->table, $fields)->fetchAll($this->getEntity());
        return $result;
    }

    public function find(string $value, string $by = "id", string|array $fields = "*"): ?Entity
    {
        $result = $this->getQueryBuilder()->select($this->table, $fields)->where($by, "=", $value)->fetch($this->getEntity());
        return $result;
    }

    public function save(Entity $entity): bool|string
    {
        $properties = $entity->getProperties();
        $result = $this->getQueryBuilder()->insert($this->table, $properties);
        return $result;
    }

    public function update(Entity $entity, string $findValue, string $findBy = "id"): int
    {
        $properties = $entity->getProperties();
        $properties["updated_at"] = date("Y-m-d H:i:s");
        $result = $this->getQueryBuilder()->update($this->table, $properties, $findBy, "=", $findValue);
        return $result;
    }

    public function delete(string $value, string $findBy = "id"): int
    {
        $result = $this->getQueryBuilder()->delete($this->table, $findBy, "=", $value);
        return $result;
    }
}
