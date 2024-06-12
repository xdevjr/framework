<?php

namespace core\library\database;

abstract class Connection
{
    private static array $activeConnections = [];
    private static array $connParameters = [
        "default" => [
            "dsn" => "",
            "username" => "",
            "password" => "",
            "options" => []
        ]
    ];
    public static function get(string $connectionName = "default"): \PDO
    {
        if (!array_key_exists($connectionName, self::$activeConnections)) {
            if (!array_key_exists($connectionName, self::$connParameters))
                throw new \Exception("Connection {$connectionName} not found in database config!");

            extract(self::$connParameters[$connectionName]);
            self::$activeConnections[$connectionName] = new \PDO($dsn, $username, $password, $options);
        }

        return self::$activeConnections[$connectionName];
    }

    /**
     * 
     * @param string $dbname can be database name or file path case driver is sqlite
     * @param string $connectionName change to create multiple connections
     */
    public static function add(
        string $username,
        string $password,
        string $driver,
        string $dbname,
        string $host = "",
        array $options = [],
        ?int $port = null,
        string $connectionName = "default"
    ): void {
        if (array_is_list($options))
            throw new \Exception("The options must be an associative array!");

        $port = !empty($port) ? "port={$port}" : "";
        $host = !empty($host) ? "host={$host};" : "";
        $dbname = $dbname !== "sqlite" ? "dbname={$dbname};" : $dbname;

        self::$connParameters[$connectionName] = [
            "dsn" => "{$driver}:{$host}{$dbname}{$port}",
            "username" => $username,
            "password" => $password,
            "options" => $options
        ];
    }

}
