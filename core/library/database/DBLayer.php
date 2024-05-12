<?php

namespace core\library\database;

abstract class DBLayer
{

    protected string $table;
    protected static ?\PDO $connection = null;
    protected static string $entityNamespace = "app\\database\\entities\\";

    public static function setConnection(array $config)
    {
        extract(array_merge(["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""], $config));
        $port = !empty($port) ? "port={$port}" : "";
        $host = $driver !== 'sqlite' ? "host={$host};" : $file;
        $dbname = !empty($dbname) ? "dbname={$dbname};" : "";
        if (!self::$connection) {
            self::$connection = new \PDO("{$driver}:{$host}{$dbname}{$port}", $username, $password, $options);
        }
    }
    public static function setEntityNamespace(string $namespace): void
    {
        self::$entityNamespace = $namespace;
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
    public function all(string $fields = "*"): ?array
    {
        $query = "select {$fields} from {$this->table}";
        $stmt = self::$connection->prepare($query);
        if ($stmt->execute()) {
            return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->getEntity());
        }
    }

    public function find(string $value, string $by = "id", string $fields = "*"): ?Entity
    {
        $query = "select {$fields} from {$this->table} where {$by} = ?";
        $stmt = self::$connection->prepare($query);
        if ($stmt->execute([$value])) {
            return $stmt->fetchObject($this->getEntity()) ?: null;
        }
    }

    public function save(Entity $entity): ?int
    {
        $attributes = $entity->getProperties();
        $query = "insert into {$this->table} (" . implode(", ", array_keys($attributes)) . ") values (:" . implode(", :", array_keys($attributes)) . ")";
        $stmt = self::$connection->prepare($query);
        if ($stmt->execute($attributes)) {
            return $stmt->rowCount();
        }
    }

    public function update(Entity $entity, string $findValue, string $findBy = "id"): ?int
    {
        $properties = $entity->getProperties();
        $properties["updated_at"] = date("Y-m-d H:i:s");
        foreach ($properties as $key => $value) {
            $placeholders[] = $key . " = :" . $key;
        }
        $properties[$findBy] = $this->find($findValue, $findBy)->$findBy ?? null;
        $query = "update {$this->table} set " . implode(", ", $placeholders) . " where {$findBy} = :{$findBy}";
        $stmt = self::$connection->prepare($query);
        if ($stmt->execute($properties)) {
            return $stmt->rowCount();
        }
    }

    public function delete(string $value, string $findBy = "id"): ?int
    {
        $query = "delete from {$this->table} where {$findBy} = :{$findBy}";
        $stmt = self::$connection->prepare($query);
        if ($stmt->execute([$findBy => $value])) {
            return $stmt->rowCount();
        }
    }
}
