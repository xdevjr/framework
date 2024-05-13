<?php
session_start();

use app\middlewares\Auth;
use app\middlewares\Teste;
use core\library\router\Router;
use core\library\database\DBLayer;

require ("../vendor/autoload.php");

// DBLayer::setConnection([
//     "driver" => "mysql",
//     "host" => "localhost",
//     "dbname" => "framework",
//     "username" => "root",
//     "password" => "",
//     "options" => [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
//         PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
//     ]
// ]);

$router = new Router("app\\controllers\\");

$router->get("/{:?num}", "HomeController@index")->name("home")->middlewares([Auth::class]);

$router->group(["prefix" => "/admin", "groupName" => "admin", "middlewares" => [Auth::class, Teste::class]], function (Router $router) {
    $router->get("/", function () {
        echo "home admin";
    }, ["name" => "home"]);
    $router->get("/user", function () {
        echo "user admin";
    })->name("user")->middlewares([Teste::class]);
});

$router->get("/teste", function () {
    echo "teste";
})->name("teste");

$router->start();