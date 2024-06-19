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

    public function query(string $query, array $binds = []): bool|\PDOStatement
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($binds);
        return $statement;
    }
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * @param \Closure $callback needs to return boolean, if false or exception transaction will be rolled back
     * @return bool
     * @throws \Exception
     */
    public function transaction(\Closure $callback): bool
    {
        if ($this->beginTransaction()) {
            try {
                $result = $callback($this);

                if (!$result) {
                    $this->rollBack();
                    return $result;
                }

                $this->commit();
                return $result;
            } catch (\Exception $exception) {
                $this->rollBack();
                throw $exception;
            }
        }

        return false;
    }
}
