<?php
use core\enums\Drivers;
use core\library\Request;
use core\library\router\Router;
use core\library\database\Entity;
use core\library\database\DBLayer;
use core\library\container\Container;
use core\library\database\Connection;
use core\library\container\Application;
use core\library\database\ConnectionParameters;

Connection::add(
    ConnectionParameters::create(
        username: "root",
        password: "",
        driver: Drivers::MYSQL,
        host: "localhost",
        database: "framework",
        options: [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
        ]
    )
);

DBLayer::setEntityNamespace('app\database\entities\\');
Entity::setModelNamespace('app\database\models\\');
Router::setDefaultNamespace('app\controllers\\');

$container = new Container;
$container->addDefinitions(root("/app/definitions/definitions.php"));

Router::setContainer($container);
Application::resolve($container);