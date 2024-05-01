<?php
session_start();

use core\library\router\Router;

// use Pecee\SimpleRouter\SimpleRouter as Router;

require ("../vendor/autoload.php");

// Router::setDefaultNamespace("app\\controllers");
// Router::get("/{page?}", "HomeController@index");

// Router::start();

$router = new Router("app\\controllers\\");

$router->match("get", "/{:?num}", "HomeController@index");

$router->start();