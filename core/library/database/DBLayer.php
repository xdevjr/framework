<?php

namespace core\library\database;

abstract class DBLayer
{

    protected string $table;
    protected Entity $entity;

    public function entity(Entity $entity): static
    {
        $this->entity = $entity;
        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder;
    }

    private function getEntity()
    {
        $reflect = new \ReflectionClass(static::class);
        $entity = ENTITY_NAMESPACE . $reflect->getShortName() . "Entity";
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

    public function save(): bool|string
    {
        $properties = $this->entity->getProperties();
        $result = $this->getQueryBuilder()->insert($this->table, $properties);
        return $result;
    }

    public function update(string $findValue, string $findBy = "id"): int
    {
        $properties = $this->entity->getProperties();
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
