<?php
session_start();

use app\middlewares\Auth;
use app\middlewares\Teste;
use core\library\router\Router;

require ("../vendor/autoload.php");

Router::setDefaultNamespace("app\\controllers\\");

Router::get("/{:?num}", "HomeController@index")->name("home")->middlewares([Auth::class]);

Router::group(["prefix" => "/admin", "groupName" => "admin", "middlewares" => [Auth::class, Teste::class]], function () {
    Router::get("/", function () {
        echo "home admin";
    }, ["name" => "home"]);
    Router::get("/user", function () {
        echo "user admin";
    })->name("user")->middlewares([]);
});

Router::get("/teste", function () {
    echo "teste";
})->name("teste");

Router::start();