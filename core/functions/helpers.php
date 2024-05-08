<?php
use Twig\Environment;
use core\library\Request;
use core\library\Session;
use core\library\Response;
use core\library\Paginator;
use core\library\Validator;
use Twig\Loader\FilesystemLoader;

function request(): Request
{
    return Request::all();
}

function response(mixed $body, bool $json = false, int $statusCode = 200, array $headers = []): mixed
{
    return (new Response($body, $statusCode, $headers))->send($json);
}

function redirect(string $url, int $statusCode = 200): void
{
    (new Response(null, $statusCode))->redirect($url);
}

function view(string $view, array $data = []): void
{
    $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/app/views');
    $twig = new Environment($loader);

    echo $twig->render("$view.twig.php", $data);
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

function root(string $path = null): string
{
    $root = dirname(__DIR__, 2);
    if ($path != null)
        $root .= $path;

    return $root;
}