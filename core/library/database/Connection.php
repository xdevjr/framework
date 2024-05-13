<?php

namespace core\library\database;

abstract class Connection
{
    private static ?\PDO $connection = null;

    public static function set(array $config): void
    {
        extract(array_merge(["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""], $config));
        $port = !empty($port) ? "port={$port}" : "";
        $host = $driver !== 'sqlite' ? "host={$host};" : $file;
        $dbname = !empty($dbname) ? "dbname={$dbname};" : "";
        if (!self::$connection) {
            self::$connection = new \PDO("{$driver}:{$host}{$dbname}{$port}", $username, $password, $options);
        }
    }

    public static function get(): ?\PDO
    {
        return self::$connection;
    }

}
