<?php

namespace core\library\database\query;

class QB
{
    public function __construct(
        private string $table,
        private \PDO $connection
    ) {
    }

    public static function create(string $table, \PDO $connection): static
    {
        return new static($table, $connection);
    }

    /**
     * @param string[] $fields Default is ["*"]
     */
    public function select(string ...$fields): Select
    {
        return new Select($this->table, $this->connection, $fields ?: ["*"]);
    }

    public function insert(array $data): Insert
    {
        return new Insert($this->table, $this->connection, $data);
    }

    public function update(array $data): Update
    {
        return new Update($this->table, $this->connection, $data);
    }

    public function delete(): Delete
    {
        return new Delete($this->table, $this->connection);
    }
}
