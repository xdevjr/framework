<?php

namespace core\library\database;

abstract class Connection
{
    private static ?\PDO $connection = null;
    private static ?string $activeConnection = null;
    private static array $connParameters = [
        "default" => [
            "dsn" => "",
            "username" => "",
            "password" => "",
            "options" => []
        ]
    ];
    public static function get(string $connection = "default"): \PDO
    {
        if (!self::$connection or self::$activeConnection !== $connection) {
            self::$activeConnection = $connection;
            extract(self::$connParameters[$connection]);
            self::$connection = new \PDO($dsn, $username, $password, $options);
        }

        return self::$connection;
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
        int $port = null,
        string $connectionName = "default"
    ): void {
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
