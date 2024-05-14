<?php

const CONNECTION = [
    "driver" => "mysql",
    "host" => "localhost",
    "dbname" => "framework",
    "username" => "root",
    "password" => "",
    "port"=> "",
    "file"=> "",
    "options" => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
    ]
];

const ENTITY_NAMESPACE = "app\\database\\entities\\";

const MODEL_NAMESPACE = "app\\database\\models\\";