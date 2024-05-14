<?php

namespace core\library\database;

abstract class Connection
{
    public static function get(): \PDO
    {
        $connection = null;
        extract(array_merge(["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""], CONNECTION));
        $port = !empty($port) ? "port={$port}" : "";
        $host = $driver !== 'sqlite' ? "host={$host};" : $file;
        $dbname = !empty($dbname) ? "dbname={$dbname};" : "";
        if (!$connection) {
            $connection = new \PDO("{$driver}:{$host}{$dbname}{$port}", $username, $password, $options);
        }

        return $connection;
    }

}
