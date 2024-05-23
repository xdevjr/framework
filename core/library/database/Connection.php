<?php

namespace core\library\database;

abstract class Connection
{
    private static ?\PDO $connection = null;
    public static function get(): \PDO
    {
        extract(array_merge(["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""], CONNECTION));
        $port = !empty($port) ? "port={$port}" : "";
        $host = $driver !== 'sqlite' ? "host={$host};" : $file;
        $dbname = !empty($dbname) ? "dbname={$dbname};" : "";
        if (!self::$connection) {
            self::$connection = new \PDO("{$driver}:{$host}{$dbname}{$port}", $username, $password, $options);
        }

        return self::$connection;
    }

}
