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
     * @return void
     */
    public static function set(array $connections): void
    {
        if (is_array($connections[array_key_first($connections)])) {
            foreach ($connections as $key => $conn) {
                extract(array_merge(["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""], $conn));
                $port = !empty($port) ? "port={$port}" : "";
                $host = $driver !== 'sqlite' ? "host={$host};" : $file;
                $dbname = !empty($dbname) ? "dbname={$dbname};" : "";

                self::$connParameters = array_merge(self::$connParameters, [
                    $key => [
                        "dsn" => "{$driver}:{$host}{$dbname}{$port}",
                        "username" => $username,
                        "password" => $password,
                        "options" => $options
                    ]
                ]);
            }
        } else {
            extract(array_merge(["driver" => "", "host" => "", "dbname" => "", "username" => "", "password" => "", "options" => [], "port" => "", "file" => ""], $connections));
            $port = !empty($port) ? "port={$port}" : "";
            $host = $driver !== 'sqlite' ? "host={$host};" : $file;
            $dbname = !empty($dbname) ? "dbname={$dbname};" : "";

            self::$connParameters["default"] = [
                "dsn" => "{$driver}:{$host}{$dbname}{$port}",
                "username" => $username,
                "password" => $password,
                "options" => $options
            ];
        }
    }

}
