<?php

namespace core\library\database;

use core\enums\Drivers;

readonly class ConnectionParameters
{
    /**
     * 
     * @param string $database can be database name or file path case driver is sqlite
     * @param string $connectionName change to create multiple connections
     */
    public function __construct(
        public Drivers $driver,
        public string $username,
        public string $password,
        public string $database,
        public string $host = "",
        public array $options = [],
        public ?int $port = null,
        public string $connectionName = "default"
    ) {
    }

    /**
     * 
     * @param string $database can be database name or file path case driver is sqlite
     * @param string $connectionName change to create multiple connections
     */
    public static function create(
        Drivers $driver,
        string $username,
        string $password,
        string $database,
        string $host = "",
        array $options = [],
        ?int $port = null,
        string $connectionName = "default"
    ): static {
        return new static($driver, $username, $password, $database, $host, $options, $port, $connectionName);
    }

    public function getName(): string
    {
        return $this->connectionName;
    }

    public function parse(): array
    {
        if (!empty($this->options) and array_is_list($this->options))
            throw new \Exception("The options must be an associative array!");

        $driver = $this->driver->value;
        $port = !empty($this->port) ? "port={$this->port}" : "";
        $host = !empty($this->host) ? "host={$this->host};" : "";
        $database = $driver !== "sqlite" ? "dbname={$this->database};" : $this->database;

        return [
            "dsn" => "{$driver}:{$host}{$database}{$port}",
            "username" => $this->username,
            "password" => $this->password,
            "options" => $this->options
        ];
    }
}
