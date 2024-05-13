<?php

namespace core\library\database;

class QueryBuilder
{
    private ?\PDO $connection = null;
    private array $query = [];

    public function select(string $table, string|array $fields = "*"): QueryBuilder
    {
        $fields = is_string($fields) ? $fields : implode(", ", $fields);
        $this->query["select"] = "select {$fields} from {$table}";
        return $this;
    }

    public function where(string $field, string $operator, string|int|array $value): QueryBuilder
    {
        $alias = $operator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($field, $value), "()")) : $this->setBindsAndGetAlias($field, $value);
        $this->query["where"] = " where {$field} {$operator} {$alias}";
        dump($this->query);
        return $this;
    }

    public function orWhere(string $field, string $operator, string|int|array $value): QueryBuilder
    {
        $alias = $operator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($field, $value), "()")) : $this->setBindsAndGetAlias($field, $value);
        $this->query["where"] .= " or {$field} {$operator} {$alias}";
        return $this;
    }

    public function andWhere(string $field, string $operator, string|int|array $value): QueryBuilder
    {
        $alias = $operator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($field, $value), "()")) : $this->setBindsAndGetAlias($field, $value);
        $this->query["where"] .= " and {$field} {$operator} {$alias}";
        return $this;
    }

    public function limit(int $value): QueryBuilder
    {
        $this->query["limit"] = " limit {$value}";
        return $this;
    }

    public function offset(int $value): QueryBuilder
    {
        $this->query["offset"] = " offset {$value}";
        return $this;
    }

    public function order(string $field, string $direction = "asc"): QueryBuilder
    {
        $this->query["order"] = " order by {$field} {$direction}";
        return $this;
    }

    public function group(string $field): QueryBuilder
    {
        $this->query["group"] = " group by {$field}";
        return $this;
    }

    public function insert(string $table, array $data): bool|string
    {
        $alias = $this->setBindsAndGetAlias($table, $data);
        $this->query["insert"] = "insert into {$table} (" . implode(",", array_keys($data)) . ") values {$alias}";
        $this->execute();
        return $this->connection->lastInsertId();
    }

    public function update(string $table, array $data, string $whereField, string $whereOperator, string|int|array $whereValue): int
    {
        $alias = $this->setUpdate($data);
        $whereAlias = $whereOperator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($whereField, $whereValue), "()")) : $this->setBindsAndGetAlias($whereField, $whereValue);
        $this->query["update"] = "update {$table} set {$alias} where {$whereField} {$whereOperator} {$whereAlias}";
        return $this->execute()->rowCount();
    }

    public function delete(string $table, string $whereField, string $whereOperator, string|int|array $whereValue): int
    {
        $whereAlias = $whereOperator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($whereField, $whereValue), "()")) : $this->setBindsAndGetAlias($whereField, $whereValue);
        $this->query["delete"] = "delete from {$table} where {$whereField} {$whereOperator} {$whereAlias}";
        return $this->execute()->rowCount();
    }

    private function setUpdate(array $data): string
    {
        $set = "";
        foreach ($data as $key => $value) {
            $set .= "{$key} = :{$key}, ";
            $this->query["binds"][$key] = $value;
        }

        $set = rtrim($set, ", ");

        return $set;
    }

    private function setBindsAndGetAlias(string $field, string|int|array $value): string
    {
        if (is_array($value)) {
            $alias = "";
            $values = [];
            foreach ($value as $key => $item) {
                $alias .= is_int($key) ? ":val{$key}, " : ":{$key}, ";
                $this->query["binds"][is_int($key) ? "val{$key}" : $key] = $item;
            }
            $alias = "(" . rtrim($alias, ", ") . ")";
        } else {
            $alias = ":{$field}";
            $this->query["binds"][$field] = $value;
        }

        return $alias;
    }

    public function getQuery(): string
    {
        extract(array_merge([
            "select" => "",
            "where" => "",
            "limit" => "",
            "offset" => "",
            "order" => "",
            "group" => "",
            "insert" => "",
            "update" => "",
            "delete" => "",
        ], $this->query));
        return "{$select}{$insert}{$update}{$delete}{$where}{$group}{$order}{$limit}{$offset}";
    }

    private function getBinds(): ?array
    {
        return $this->query["binds"] ?? null;
    }

    public function execute(): bool|\PDOStatement
    {
        dump($this->query);
        $this->getConnection();
        $stmt = $this->connection->prepare($this->getQuery());
        if ($stmt->execute($this->getBinds())) {
            $this->query = [];
            return $stmt;
        }
        return false;
    }

    public function fetch(?string $entity = null): bool|object
    {
        if ($entity and !class_exists($entity)) {
            throw new \Exception("Entity {$entity} does not exist!");
        } elseif ($entity and !new $entity instanceof Entity) {
            throw new \Exception("Entity {$entity} needs to implement the " . Entity::class . "!");
        }

        return $this->execute()->fetchObject($entity);
    }

    public function fetchAll(?string $entity = null): array
    {
        if ($entity and !class_exists($entity)) {
            throw new \Exception("Entity {$entity} does not exist!");
        } elseif ($entity and !new $entity instanceof Entity) {
            throw new \Exception("Entity {$entity} needs to implement the " . Entity::class . "!");
        }

        return $this->execute()->fetchAll(\PDO::FETCH_CLASS, $entity);
    }

    public function rowCount(): int
    {
        return $this->execute()->rowCount();
    }

    private function getConnection(): void
    {
        extract([
            "driver" => "mysql",
            "host" => "localhost",
            "dbname" => "framework",
            "username" => "root",
            "password" => "",
            "options" => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        ]);
        $port = !empty($port) ? "port={$port}" : "";
        $host = $driver !== 'sqlite' ? "host={$host};" : $file;
        $dbname = !empty($dbname) ? "dbname={$dbname};" : "";
        $this->connection = new \PDO("{$driver}:{$host}{$dbname}{$port}", $username, $password, $options);
    }

}
