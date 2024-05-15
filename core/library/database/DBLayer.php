<?php

namespace core\library\database;

abstract class DBLayer
{

    protected string $table;
    protected Entity $entity;
    protected array|Entity $results;

    public function entity(Entity $entity): static
    {
        $this->entity = $entity;
        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder;
    }

    private function getEntity(): string
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
    public function all(string|array $fields = "*"): static
    {
        $result = $this->getQueryBuilder()->select($this->table, $fields)->fetchAll($this->getEntity());
        $this->results = $result;
        return $this;
    }

    public function find(string|array $value, string $by = "id", string $operator = "=", string|array $fields = "*"): static
    {
        $query = $this->getQueryBuilder()->select($this->table, $fields)->where($by, $operator, $value);
        if ($query->rowCount() > 1) {
            $result = $query->fetchAll($this->getEntity());
        } else {
            $result = $query->fetch($this->getEntity());
        }
        if ($result)
            $this->results = $result;

        return $this;
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

    public function getResult(): array|Entity|null
    {
        return $this->results ?? null;
    }

    public function relationWith(string $model, string $relationField, string $findField = "id", string $alias = "relation"): static
    {
        if (!class_exists($model)) {
            throw new \Exception("Model {$model} does not exist!");
        }
        $model = new $model;
        if (!$model instanceof DBLayer) {
            throw new \Exception("Model {$model} needs to implement the " . DBLayer::class . "!");
        }

        if (is_array($this->results)) {
            $value = array_column($this->results, $findField);
            $finds = $model->find($value, $relationField, "in")->getResult();

            if ($finds) {
                foreach ($this->results as $result) {
                    foreach ($finds as $find) {
                        if ($find->$relationField == $result->$findField)
                            $result->$alias[] = $find;
                    }

                    $relations[] = $result;
                }
            }
            $this->results = array_map(function ($value) use ($alias) {
                if (isset ($value->$alias) and count($value->$alias) == 1)
                    $value->$alias = $value->$alias[0];
                return $value;
            }, $relations);
        } else {
            $this->results->$alias = $model->find($this->results->$findField, $relationField)->getResult();
        }

        return $this;
    }
}
