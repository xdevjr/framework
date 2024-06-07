<?php

namespace core\library\database\query;

class Delete
{
    private array $query = [
        "delete" => "",
        "where" => ""
    ];
    private array $binds = [];
    public function __construct(
        private string $table,
        private \PDO $connection
    )
    {
        $this->query["delete"] = "delete from {$this->table}";
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
        return $delete.$where;
    }

    public function getBinds(): array
    {
        return $this->binds;
    }

    public function execute(): bool
    {
        $statement = $this->connection->prepare($this->getQuery());
        $statement->execute($this->getBinds());
        return $statement->rowCount() > 0;
    }

}
