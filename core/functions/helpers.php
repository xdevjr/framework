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

function validate(array|string|int|float|bool|null $data, array|string $rules, array $customMessages = []): Validator
{
    return new Validator($data, $rules, $customMessages);
}

/**
 * Returns the root path of the project with an optional relative path appended.
 *
 * @param string $path The optional relative path to be appended to the root path.
 * @return string The root path with the given path appended.
 */
function root(string $path = null): string
{
    $path = $path ? str_replace(["\\", "/"], DIRECTORY_SEPARATOR, trim($path, "\/")) : "";
    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $path;

    if (is_dir($path))
        $path .= DIRECTORY_SEPARATOR;

    return $path;
}

function slugify(string $string, string $delimiter = "-"): string
{
    $slug = transliterator_transliterate("Any-Latin; Latin-ASCII; Lower; NFC; NFD; [:Punctuation:] Remove;  [:Symbol:] Remove", trim($string));
    $slug = preg_replace(["/\s+/", "/[^\-\w]/"], [$delimiter, ""], $slug);

    return $slug;
}

function url(string $nameOrPath, array $parameters = [], array $getParameters = []): string
{
    return Router::getUri($nameOrPath, $parameters, $getParameters);
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
    $dom = new DOMDocument();
    $input = $dom->createElement("input");
    $input->setAttribute("type", "hidden");
    $input->setAttribute("name", "__csrf");
    $input->setAttribute("value", session()->getCsrfToken());
    $dom->appendChild($input);

    return $dom->saveHTML();
}