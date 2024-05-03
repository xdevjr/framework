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

$router->start();