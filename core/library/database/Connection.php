<?php

namespace core\library\database;

abstract class Connection
{
    private static ?\PDO $connection = null;
    private static array $connParameters = [
        "dsn" => "",
        "username" => "",
        "password" => "",
        "options" => []
    ];
    public static function get(): \PDO
    {
        if (!self::$connection) {
            extract(self::$connParameters);
            self::$connection = new \PDO($dsn, $username, $password, $options);
        }

        return self::$connection;
    }

    /**
     * 
     * @param array $conn ["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""]
     * @return void
     */
    public static function set(array $conn): void
    {
        extract(array_merge(["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""], $conn));
        $port = !empty($port) ? "port={$port}" : "";
        $host = $driver !== 'sqlite' ? "host={$host};" : $file;
        $dbname = !empty($dbname) ? "dbname={$dbname};" : "";

        self::$connParameters = [
            "dsn" => "{$driver}:{$host}{$dbname}{$port}",
            "username" => $username,
            "password" => $password,
            "options" => $options
        ];
    }

}
