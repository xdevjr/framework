<?php

namespace core\library\database;
use core\library\Paginator;

class QueryBuilder
{
    private ?\PDO $connection = null;
    private array $query = [];
    public ?string $paginateLinks = null;

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

    public function query(string $query, array $binds = [])
    {
        $this->query["query"] = $query;
        $this->query["binds"] = $binds;
        return $this;
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
                if (!isset($this->query["binds"][is_int($key) ? "val{$key}" : $key])) {
                    $alias .= is_int($key) ? ":val{$key}, " : ":{$key}, ";
                    $this->query["binds"][is_int($key) ? "val{$key}" : $key] = $item;
                } else {
                    $i = uniqid();
                    $alias .= is_int($key) ? ":val{$key}{$i}, " : ":{$key}{$i}, ";
                    $this->query["binds"][is_int($key) ? "val{$key}{$i}" : "{$key}{$i}"] = $item;
                }
            }
            $alias = "(" . rtrim($alias, ", ") . ")";
        } else {
            if (!isset($this->query["binds"][$field])) {
                $alias = ":{$field}";
                $this->query["binds"][$field] = $value;
            } else {
                $i = uniqid();
                $alias = ":{$field}{$i}";
                $this->query["binds"]["{$field}{$i}"] = $value;
            }
        }

        return $alias;
    }

    public function getQuery(): string
    {
        extract(array_merge([
            "query"=> "",
            "insert" => "",
            "update" => "",
            "delete" => "",
            "select" => "",
            "where" => "",
            "limit" => "",
            "offset" => "",
            "order" => "",
            "group" => "",
        ], $this->query));
        return "{$query}{$insert}{$update}{$delete}{$select}{$where}{$group}{$order}{$limit}{$offset}";
    }

    public function paginate(int $limit, int $currentPage = 1, string $link = "?page=", int $maxLinksPerPage = 5)
    {
        $paginate = new Paginator($currentPage, $limit, $this->rowCount(), $link, $maxLinksPerPage);

        $this->limit($paginate->getLimit());
        $this->offset($paginate->getOffset());
        $this->paginateLinks = $paginate->generateLinks();

        return $this;
    }

    public function getBinds(): ?array
    {
        return $this->query["binds"] ?? null;
    }

    public function beginTransaction(): bool
    {
        return Connection::get()->beginTransaction();
    }

    public function commit(): bool
    {
        return Connection::get()->commit();
    }

    public function rollBack(): bool
    {
        return Connection::get()->rollBack();
    }

    public function reset(): QueryBuilder
    {
        $this->query = [];
        $this->paginateLinks = null;

        return $this;
    }

    public function execute(): bool|\PDOStatement
    {
        $this->connection = Connection::get();
        $stmt = $this->connection->prepare($this->getQuery());
        if ($stmt->execute($this->getBinds())) {
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

}
