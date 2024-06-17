<?php

namespace core\library\database;

use core\library\database\query\QB;

abstract class Connection
{
    private static array $activeConnections = [];
    private static array $connParameters = [];
    public static function get(string $connectionName = "default"): \PDO
    {
        if (!array_key_exists($connectionName, self::$activeConnections)) {
            $findConnectionParameters = array_values(array_filter(self::$connParameters, fn($connection) => $connection === $connectionName, ARRAY_FILTER_USE_KEY));
            if (!$findConnectionParameters)
                throw new \Exception("Connection {$connectionName} not found in database config!");

            extract(($findConnectionParameters[0])->parse());
            self::$activeConnections[$connectionName] = new \PDO($dsn, $username, $password, $options);
        }

        return self::$activeConnections[$connectionName];
    }

    public static function createQueryBuilder(string $table, string $connectionName = "default"): QB
    {
        return QB::create($table, self::get($connectionName));
    }

    public static function add(ConnectionParameters ...$connectionParameters): void
    {
        foreach ($connectionParameters as $connectionParameter) {
            if (isset(self::$connParameters[$connectionParameter->getName()]))
                throw new \Exception("Connection {$connectionParameter->getName()} already exists in database config!");

            self::$connParameters[$connectionParameter->getName()] = $connectionParameter;
        }
    }

}
