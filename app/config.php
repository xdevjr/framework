<?php
use core\library\database\Connection;
use core\library\database\DBLayer;
use core\library\database\Entity;

Connection::add(
    username: "root",
    password: "",
    driver: "mysql",
    host: "localhost",
    dbname: "framework",
    options: [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
    ]
);

DBLayer::setEntityNamespace("app\\database\\entities\\");

Entity::setModelNamespace("app\\database\\models\\");