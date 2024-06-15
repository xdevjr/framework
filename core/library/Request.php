<?php

namespace core\library;

readonly class Request
{
    public function __construct(
        private array $get,
        private array $post,
        private array $json,
        private array $files,
        private array $cookies,
        private array $server
    ) {
    }

    public static function create(): static
    {
        $input = file_get_contents('php://input');
        $_JSON = json_validate($input) ? json_decode($input, true) : [];
        return new static($_GET, $_POST, $_JSON, $_FILES, $_COOKIE, $_SERVER);
    }

    private function filter(array $request, bool $array = false): object|array
    {
        $filter = filter_var_array(array_map(function ($value) {
            if (is_array($value)) {
                return array_map(function ($value) {
                    return trim(strip_tags($value));
                }, $value);
            }

            return trim(strip_tags($value));
        }, $request));

        return $array ? $filter : (object) $filter;
    }

    public function get(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter($this->get, $array);

        return $array ? $this->get : (object) $this->get;
    }
    public function post(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter($this->post, $array);

        return $array ? $this->post : (object) $this->post;
    }
    public function files(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter($this->files, $array);

        return $array ? $this->files : (object) $this->files;
    }
    public function cookies(bool $filter = true, bool $array = true): object|array
    {
        if ($filter)
            return $this->filter($this->cookies, $array);

        return $array ? $this->cookies : (object) $this->cookies;
    }
    public function server(bool $filter = true, bool $array = true): object|array
    {
        if ($filter)
            return $this->filter($this->server, $array);

        return $array ? $this->server : (object) $this->server;
    }

    public function json(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter($this->json, $array);

        return $array ? $this->json : (object) $this->json;
    }

    public function all(bool $filter = true, bool $array = true): object|array
    {
        $request = array_merge($this->get, $this->post, $this->json, $this->files);
        if ($filter)
            return $this->filter($request, $array);

        return $array ? $request : (object) $request;
    }

    public function only(array $keys, bool $filter = true, bool $array = true): object|array
    {
        if (!array_is_list($keys))
            throw new \Exception("The keys cannot be an associative array!");

        $request = array_filter($this->all($filter), fn($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);

        return $array ? $request : (object) $request;
    }

    public function input(string $key, mixed $default = null, bool $filter = true): mixed
    {
        return $this->all($filter)[$key] ?? $default;
    }
}
