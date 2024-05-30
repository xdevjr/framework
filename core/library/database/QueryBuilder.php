<?php

namespace core\library\database;

use core\library\Paginator;

class QueryBuilder
{
    private ?\PDO $connection = null;
    private array $query = [];
    public ?Paginator $paginator = null;

    public function __construct(private string $table)
    {
    }
    public static function table(string $table): QueryBuilder
    {
        return new static($table);
    }

    public function select(array $fields = ["*"]): QueryBuilder
    {
        $fields = "{$this->table}." . implode(", {$this->table}.", $fields);
        $this->query["fields"] = $fields;
        $this->query["select"] = "select {$fields} from {$this->table} ";
        return $this;
    }

    public function where(string $field, string $operator, string|int|array $value): QueryBuilder
    {
        $alias = $operator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($field, $value), "()")) : $this->setBindsAndGetAlias($field, $value);
        $this->query["where"] = " where {$this->table}.{$field} {$operator} {$alias}";
        return $this;
    }

    public function orWhere(string $field, string $operator, string|int|array $value): QueryBuilder
    {
        $alias = $operator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($field, $value), "()")) : $this->setBindsAndGetAlias($field, $value);
        $this->query["where"] .= " or {$this->table}.{$field} {$operator} {$alias}";
        return $this;
    }

    public function andWhere(string $field, string $operator, string|int|array $value): QueryBuilder
    {
        $alias = $operator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($field, $value), "()")) : $this->setBindsAndGetAlias($field, $value);
        $this->query["where"] .= " and {$this->table}.{$field} {$operator} {$alias}";
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
        $this->query["order"] = " order by {$this->table}.{$field} {$direction}";
        return $this;
    }

    public function group(string $field): QueryBuilder
    {
        $this->query["group"] = " group by {$this->table}.{$field}";
        return $this;
    }

    public function innerJoin(string $table, array $fields): QueryBuilder
    {
        $fields = $this->query["fields"] . ", {$table}." . implode(", {$table}.", $fields);
        $this->query["select"] = "select {$fields} from {$this->table} ";
        $this->query["join"] = " inner join {$table}";
        $this->query["joinTable"] = $table;
        return $this;
    }

    public function leftJoin(string $table, array $fields): QueryBuilder
    {
        $fields = $this->query["fields"] . ", {$table}." . implode(", {$table}.", $fields);
        $this->query["select"] = "select {$fields} from {$this->table} ";
        $this->query["join"] = " left join {$table}";
        $this->query["joinTable"] = $table;
        return $this;
    }

    public function rightJoin(string $table, array $fields): QueryBuilder
    {
        $fields = $this->query["fields"] . ", {$table}." . implode(", {$table}.", $fields);
        $this->query["select"] = "select {$fields} from {$this->table} ";
        $this->query["join"] = " right join {$table}";
        $this->query["joinTable"] = $table;
        return $this;
    }

    public function on(string $field, string $operator, string $joinField): QueryBuilder
    {
        $this->query["on"] = " on {$this->table}.{$field} {$operator} {$this->query['joinTable']}.{$joinField}";
        return $this;
    }

    public function andOn(string $joinField, string $operator, string $field): QueryBuilder
    {
        $this->query["on"] .= " {$this->query['joinTable']}.{$joinField} {$operator} {$this->table}.{$field}";
        return $this;
    }

    public function orOn(string $joinField, string $operator, string $field): QueryBuilder
    {
        $this->query["on"] .= " {$this->query['joinTable']}.{$joinField} {$operator} {$this->table}.{$field}";
        return $this;
    }

    public function insert(array $data): bool|string
    {
        $alias = $this->setBindsAndGetAlias($this->table, $data);
        $this->query["query"] = "insert into {$this->table} (" . implode(",", array_keys($data)) . ") values {$alias}";
        $this->execute();
        return $this->connection->lastInsertId();
    }

    public function update(array $data, string $whereField, string $whereOperator, string|int|array $whereValue): int
    {
        $alias = $this->setUpdate($data);
        $whereAlias = $whereOperator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($whereField, $whereValue), "()")) : $this->setBindsAndGetAlias($whereField, $whereValue);
        $this->query["query"] = "update {$this->table} set {$alias} where {$whereField} {$whereOperator} {$whereAlias}";
        return $this->execute()->rowCount();
    }

    public function delete(string $whereField, string $whereOperator, string|int|array $whereValue): int
    {
        $whereAlias = $whereOperator == "between" ? str_replace(",", " and", trim($this->setBindsAndGetAlias($whereField, $whereValue), "()")) : $this->setBindsAndGetAlias($whereField, $whereValue);
        $this->query["query"] = "delete from {$this->table} where {$whereField} {$whereOperator} {$whereAlias}";
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
                $alias .= "?, ";
                $this->query["binds"][] = $item;
            }
            $alias = "(" . rtrim($alias, ", ") . ")";
        } else {
            $alias = "?";
            $this->query["binds"][] = $value;
        }

        return $alias;
    }

    public function getQuery(): string
    {
        extract(array_merge([
            "query" => "",
            "select" => "",
            "where" => "",
            "limit" => "",
            "offset" => "",
            "order" => "",
            "group" => "",
            "join" => "",
            "on" => "",
        ], $this->query));
        return "{$query}{$select}{$join}{$on}{$where}{$group}{$order}{$limit}{$offset}";
    }

    public function paginate(int $limit, int $currentPage = 1, string $link = "?page=", int $maxLinksPerPage = 5)
    {
        $paginate = new Paginator($currentPage, $limit, $this->rowCount(), $link, $maxLinksPerPage);

        $this->limit($paginate->getLimit());
        $this->offset($paginate->getOffset());
        $this->paginator = $paginate;

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

    public function fetch(?string $entity = null): ?object
    {
        if ($entity and !class_exists($entity)) {
            throw new \Exception("Entity {$entity} does not exist!");
        } elseif ($entity and !new $entity instanceof Entity) {
            throw new \Exception("Entity {$entity} needs to implement the " . Entity::class . "!");
        }

        return $this->execute()->fetchObject($entity) ?: null;
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
