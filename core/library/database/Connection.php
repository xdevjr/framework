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
     * @param array $connections ["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""]
     * @param string $connectionName change to create multiple connections
     */
    public static function set(array $connection, string $connectionName = "default"): void
    {
        self::$connParameters[$connectionName] = self::transformConnectionParameters($connection);
    }

    private static function transformConnectionParameters(array $parameters): array
    {
        extract(self::filterConnectionParameters($parameters));
        $port = !empty($port) ? "port={$port}" : "";
        $host = $driver !== 'sqlite' ? "host={$host};" : $file;
        $dbname = !empty($dbname) ? "dbname={$dbname};" : "";

        return [
            "dsn" => "{$driver}:{$host}{$dbname}{$port}",
            "username" => $username,
            "password" => $password,
            "options" => $options
        ];
    }

    private static function filterConnectionParameters(array $parameters): array
    {
        $validParameters = [
            "driver" => "",
            "host" => "",
            "dbname" => "",
            "username" => "",
            "password" => "",
            "options" => [],
            "port" => "",
            "file" => ""
        ];

        array_walk($parameters, function ($value, $key) use ($validParameters) {
            if (!in_array($key, array_keys($validParameters), true) and !in_array(gettype($value), array_map("gettype", $validParameters), true))
                throw new \Exception("Invalid connection parameters");
        });

        return array_merge($validParameters, $parameters);
    }

}
