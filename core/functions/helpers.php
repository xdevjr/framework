<?php
use core\library\router\Router;
use Twig\Environment;
use core\library\Request;
use core\library\Session;
use core\library\Response;
use core\library\Paginator;
use core\library\Validator;
use Twig\Loader\FilesystemLoader;

function request(): Request
{
    return Request::create();
}

/**
 * @param array $headers example: ["Content-Type" => "application/json"]
 * @param bool $json if set true the body will be json encoded and the content type header will be set
 */
function response(mixed $body, int $statusCode = 200, array $headers = [], bool $json = false): Response
{
    return new Response($body, $statusCode, $headers, $json);
}

function redirect(string $url): void
{
    Response::redirect($url);
    exit;
}

function view(string $view, array $data = []): string
{
    $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/app/views');
    $twig = new Environment($loader);

    return $twig->render("$view.twig.php", $data);
}

function session(): Session
{
    return new Session();
}
function paginator(int $currentPage, int $itemsPerPage, int $totalItems, string $link, int $maxLinksPerPage = 5): Paginator
{
    return new Paginator($currentPage, $itemsPerPage, $totalItems, $link, $maxLinksPerPage);
}

function validate(): Validator
{
    return new Validator();
}

function root(string $path = ""): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . trim($path, "\/") . DIRECTORY_SEPARATOR;
}

function slug(string $string): string
{
    $slug = iconv("utf-8", "us-ascii//TRANSLIT", strtolower(trim($string)));
    $slug = preg_replace(["/\s+/", "/[^\-\w]/"], ["-", ""], $slug);

    return $slug;
}

function url(string $nameOrPath, array $parameters = [], array $getParameters = []): string
{
    return Router::getUrl($nameOrPath, $parameters, $getParameters);
}

function flash(string $key, mixed $value, string $redirectTo = null): void
{
    session()->flash($key, $value);
    if ($redirectTo)
        redirect($redirectTo);
}

function flashArray(array $data, string $redirectTo = null): void
{
    session()->flashArray($data);
    if ($redirectTo)
        redirect($redirectTo);
}

function csrfCreateAndCheck(): void
{
    session()->createCsrfToken();
    session()->checkCsrfToken();
}
function csrf(): string
{
    return '<input type="hidden" name="__csrf" value="' . session()->getCsrfToken() . '">';
}