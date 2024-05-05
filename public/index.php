<?php
session_start();

use app\middlewares\Auth;
use app\middlewares\Teste;
use core\library\router\Router;

// use Pecee\SimpleRouter\SimpleRouter as Route;

require ("../vendor/autoload.php");

// Route::setDefaultNamespace("app\\controllers");
// Route::get("/{page?}", "HomeController@index");

// Route::start();

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