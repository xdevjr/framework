<?php
session_start();

use core\library\router\Router;

// use Pecee\SimpleRouter\SimpleRouter as Router;

require ("../vendor/autoload.php");

// Router::setDefaultNamespace("app\\controllers");
// Router::get("/{page?}", "HomeController@index");

// Router::start();

$router = new Router("app\\controllers\\");

$router->get("/{:?num}", "HomeController@index")->name("home");

$router->start();