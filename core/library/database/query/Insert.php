<?php

namespace core\library\database\query;

class Insert
{
    private string $query = "";
    private array $binds = [];
    public function __construct(
        private string $table,
        private \PDO $connection,
        private array $data
    ) {
        $alias = $this->setBindsAndGetAlias($data);
        $this->query = "insert into {$this->table} (" . implode(",", array_keys($data)) . ") values {$alias}";
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
        return $this->query;
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
