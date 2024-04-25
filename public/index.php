<?php
use core\library\Request;
session_start();
use Pecee\SimpleRouter\SimpleRouter as Router;

require ("../vendor/autoload.php");

Router::setDefaultNamespace("app\\controllers");
Router::get("/{page?}", "HomeController@index", ["parameters" => [Request::all()]]);

Router::start();