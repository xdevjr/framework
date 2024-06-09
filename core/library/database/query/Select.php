<?php

namespace core\library\database\query;

use core\library\Paginator;

class Select
{
    private array $query = [
        "select" => "",
        "where" => "",
        "limit" => "",
        "offset" => "",
        "order" => "",
        "group" => "",
        "join" => "",
        "fields" => "",
        "total" => "",
    ];
    private array $binds = [];
    public function __construct(
        private string $table,
        private \PDO $connection,
        private array $fields = ["*"]
    ) {
        $fields = implode(", ", $fields);
        $this->query["fields"] = $fields;
        $this->query["select"] = "select {$fields} from {$table}";
    }

    public function where(string $field, string $operator, string|int|array $value): static
    {
        if (is_array($value) and !in_array($operator, ["in", "not in", "between"]))
            throw new \Exception("You can only use operators in, not in or between from array values!");

        $alias = $this->setBindsAndGetAlias($value);
        $alias = $operator == "between" ? str_replace(",", " and", trim($alias, "()")) : $alias;
        $this->query["where"] = " where {$field} {$operator} {$alias}";
        return $this;
    }

    public function andWhere(string $field, string $operator, string|int|array $value): static
    {
        if (empty($this->query["where"]))
            throw new \Exception("You must use where before using andWhere!");

        if (is_array($value) and !in_array($operator, ["in", "not in", "between"]))
            throw new \Exception("You can only use operators in, not in or between from array values!");

        $alias = $this->setBindsAndGetAlias($value);
        $alias = $operator == "between" ? str_replace(",", " and", trim($alias, "()")) : $alias;
        $this->query["where"] .= " and {$field} {$operator} {$alias}";
        return $this;
    }

    public function orWhere(string $field, string $operator, string|int|array $value): static
    {
        if (empty($this->query["where"]))
            throw new \Exception("You must use where before using orWhere!");

        if (is_array($value) and !in_array($operator, ["in", "not in", "between"]))
            throw new \Exception("You can only use operators in, not in or between from array values!");

        $alias = $this->setBindsAndGetAlias($value);
        $alias = $operator == "between" ? str_replace(",", " and", trim($alias, "()")) : $alias;
        $this->query["where"] .= " or {$field} {$operator} {$alias}";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->query["limit"] = " limit {$limit}";
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->query["offset"] = " offset {$offset}";
        return $this;
    }

    public function order(string $field, string $direction = "asc"): static
    {
        $alias = $this->setBindsAndGetAlias($field);
        $this->query["order"] = " order by {$alias} {$direction}";
        return $this;
    }

    public function group(string $field): static
    {
        $this->query["group"] = " group by {$field}";
        return $this;
    }

    public function join(string $table, array $fields, string $type = "inner"): static
    {
        $fields = $this->query["fields"] . ", " . implode(", ", $fields);
        $this->query["select"] = "";
        $this->query["join"] = "select {$fields} from {$this->table} {$type} join {$table}";
        return $this;
    }

    public function on(string $field, string $operator, string $joinField): static
    {
        if (empty($this->query["join"]))
            throw new \Exception("You must use join before using on!");

        $this->query["join"] .= " on {$field} {$operator} {$joinField}";
        return $this;
    }

    public function using(string $field): static
    {
        if (empty($this->query["join"]))
            throw new \Exception("You must use join before using using!");

        $this->query["join"] .= " using({$field})";
        return $this;
    }

    /**
     * @param Paginator $paginator Reference to paginator instance
     */
    public function paginate(&$paginator, int $limit, int $currentPage = 1, string $link = "?page=", int $maxLinksPerPage = 5): static
    {
        $totalItems = $this->totalItems();
        $paginator = new Paginator($currentPage, $limit, $totalItems, $link, $maxLinksPerPage);
        $this->limit($paginator->getLimit())->offset($paginator->getOffset());
        return $this;
    }

    private function setBindsAndGetAlias(array|string|int $data): string
    {
        if (is_array($data)) {
            $alias = "";
            foreach ($data as $key => $item) {
                $alias .= "?, ";
                $this->binds[] = $item;
            }
            $alias = "(" . rtrim($alias, ", ") . ")";
        } else {
            $alias = "?";
            $this->binds[] = $data;
        }

        return $alias;
    }

    public function getQuery(): string
    {
        extract($this->query);
        return $select . $join . $where . $group . $order . $limit . $offset;
    }

    public function getBinds(): array
    {
        return $this->binds;
    }

    public function debug(): array
    {
        return [
            "query" => $this->getQuery(),
            "binds" => $this->getBinds()
        ];
    }

    public function execute(): \PDOStatement
    {
        $statement = $this->connection->prepare($this->getQuery());
        $statement->execute($this->getBinds());
        return $statement;
    }

    public function fetchObject(string $className = "stdClass"): ?object
    {
        return $this->execute()->fetchObject($className) ?: null;
    }

    public function fetch(int $fetchMode = \PDO::FETCH_ASSOC): ?array
    {
        return $this->execute()->fetch($fetchMode) ?: null;
    }

    public function fetchAll(string $className = "stdClass", int $fetchMode = \PDO::FETCH_CLASS): array
    {
        return $this->execute()->fetchAll($fetchMode, $className);
    }

    public function totalItems(): int
    {
        $query = "select count(*) as total " . substr($this->getQuery(), strpos($this->getQuery(), "from"));
        $statement = $this->connection->prepare($query);
        $statement->execute($this->getBinds());
        $total = $statement->fetchColumn();

        return $total;
    }
}
