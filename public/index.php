<?php
use Pecee\SimpleRouter\SimpleRouter as Router;

require ("../vendor/autoload.php");

Router::setDefaultNamespace("app\\controllers");
Router::get("/{page?}", "HomeController@index");

Router::start();