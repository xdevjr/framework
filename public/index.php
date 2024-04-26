<?php
session_start();
use Pecee\SimpleRouter\SimpleRouter as Router;

require ("../vendor/autoload.php");

Router::setDefaultNamespace("app\\controllers");
Router::get("/", "HomeController@index");

Router::start();