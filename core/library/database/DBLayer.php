<?php

namespace core\library\database;

use core\library\database\query\QB;
use core\library\database\query\Select;

abstract class DBLayer
{

    protected string $table;
    protected string $db = "default";
    public ?QB $queryBuilder = null;
    protected ?Select $currentSelect = null;
    protected array|Entity|null $results = null;
    protected static string $entityNamespace = "app\\database\\entities\\";

    public function __construct(
        public ?Entity $entity = null
    ) {
        $this->queryBuilder = QB::create($this->table, $this->db);
    }

    public static function setEntityNamespace(string $namespace): void
    {
        self::$entityNamespace = $namespace;
    }

    private function getEntity(): string
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

    /**
     * @param string[] $fields Default is ["*"]
     */
    public function all(string ...$fields): static
    {
        $this->currentSelect = $this->queryBuilder->select(...$fields);
        $this->results = $this->currentSelect->fetchAll($this->getEntity());
        return $this;
    }

    /**
     * @param string[] $fields Default is ["*"]
     */
    public function find(array|int|string $value, string $by = "id", string $operator = "=", string ...$fields): static
    {
        $this->currentSelect = $this->queryBuilder->select(...$fields)->where($by, $operator, $value);
        if ($this->currentSelect->totalItems() > 1) {
            $result = $this->currentSelect->fetchAll($this->getEntity());
        } else {
            $result = $this->currentSelect->fetchObject($this->getEntity());
        }
        if ($result)
            $this->results = $result;

        return $this;
    }

    /**
     * @param \core\library\Paginator $paginator Reference to paginator instance
     */
    public function paginate(&$paginator, int $itemsPerPage, int $currentPage, string $link = "?page=", int $maxLinksPerPage = 5): static
    {
        if (!$this->currentSelect)
            throw new \Exception("Please use all() or find() before paginate()!");

        $this->results = $this->currentSelect->paginate($paginator, $itemsPerPage, $currentPage, $link, $maxLinksPerPage)->fetchAll($this->getEntity());

        return $this;
    }

    public function insert(): bool
    {
        if (!$this->entity)
            throw new \Exception("Please set the entity before insert()!");

        $properties = $this->entity->getProperties();
        $result = $this->queryBuilder->insert($properties)->execute();
        return $result;
    }

    public function update(array|int|string $value, string $field = "id", string $operator = "="): bool
    {
        if (!$this->entity)
            throw new \Exception("Please set the entity before update()!");

        $properties = $this->entity->getProperties();
        $result = $this->queryBuilder->update($properties)->where($field, $operator, $value)->execute();
        return $result;
    }

    public function delete(array|int|string $value, string $field = "id", string $operator = "="): bool
    {
        $result = $this->queryBuilder->delete()->where($field, $operator, $value)->execute();
        return $result;
    }

    public function result(): array|Entity|null
    {
        return $this->results ?? null;
    }

    public function relationWith(string $model, string $foreignKey, string $localKey = "id", string $alias = "relation"): static
    {
        if (!class_exists($model))
            throw new \Exception("Model {$model} does not exist!");

        $model = new $model;
        if (!$model instanceof DBLayer)
            throw new \Exception("Model {$model} needs to extends the " . DBLayer::class . "!");

        if (!$this->results)
            throw new \Exception("Unable to create relationship, no records found!");

        if (is_array($this->results)) {
            $value = array_column($this->results, $localKey);
            $finds = $model->find($value, $foreignKey, "in")->result();

            if ($finds) {
                foreach ($this->results as $result) {
                    foreach ($finds as $find) {
                        if ($find->$foreignKey == $result->$localKey)
                            $result->$alias[] = $find;
                    }

                    $relations[] = $result;
                }
            }
            $this->results = array_map(function ($value) use ($alias) {
                if (isset($value->$alias) and count($value->$alias) == 1)
                    $value->$alias = $value->$alias[0];
                return $value;
            }, $relations ?? $this->results);
        } else {
            $this->results->$alias = $model->find($this->results->$localKey, $foreignKey)->result();
        }

        return $this;
    }
}
