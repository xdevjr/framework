<?php
use core\library\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;
use Pecee\Http\Url;

function request(): Request
{
    return Request::all();
}

function url(?string $name = null, $parameters = null, ?array $getParams = null): Url
{
    return Router::getUrl($name, $parameters, $getParams);
}