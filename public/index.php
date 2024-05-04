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

$router->group(["prefix" => "/admin"], function ($router) {
    $router->get("/", function () {
        echo "home admin";
    })->name("admin");
    $router->get("/user", function () {
        echo "user admin";
    })->name("user.admin");
});

$router->get("/teste", function () {
    echo "teste";
})->name("teste");

$router->start();