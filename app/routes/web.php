<?php
use app\middlewares\Auth;
use app\middlewares\CSRF;
use app\middlewares\Teste;
use core\library\router\Router;

Router::group(["groupName" => "web", "middlewares" => [CSRF::class]], function () {

    Router::get("/{:?num}", "HomeController@index")->name("home");

    Router::group(["prefix" => "/admin", "groupName" => "admin", "middlewares" => [Auth::class, Teste::class]], function () {

        Router::get("/", function () {
            echo "home admin";
        }, ["name" => "home"]);

        Router::get("/user", function () {
            echo "user admin";
        })->name("user")->overrideMiddlewares();

    });

    Router::get("/teste", function () {
        echo "teste";
    })->name("teste");

});