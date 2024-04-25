<?php
use Pecee\Http\Url;
use core\library\Request;
use core\library\Response;
use Pecee\SimpleRouter\SimpleRouter as Router;

function request(): Request
{
    return Request::all();
}

function response(mixed $body, int $statusCode = 200, array $headers = []): Response
{
    return new Response($body, $statusCode, $headers);
}

function url(?string $name = null, $parameters = null, ?array $getParams = null): Url
{
    return Router::getUrl($name, $parameters, $getParams);
}