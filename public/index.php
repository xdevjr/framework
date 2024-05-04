<?php
session_start();

use core\library\router\Router;

// use Pecee\SimpleRouter\SimpleRouter as Route;

require ("../vendor/autoload.php");

// Route::setDefaultNamespace("app\\controllers");
// Route::get("/{page?}", "HomeController@index");

// Route::start();

$router = new Router("app\\controllers\\");

$router->get("/{:?num}", "HomeController@index")->name("home");

$router->group(["prefix" => "/admin", "groupName" => "admin"], function ($router) {
    $router->get("/", function () {
        echo "home admin";
    }, ["name"=> "home"]);
    $router->get("/user", function () {
        echo "user admin";
    })->name("user");
});

$router->get("/teste", function () {
    echo "teste";
})->name("teste");

$router->start();