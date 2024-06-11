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
        $json = json_decode(file_get_contents('php://input'), true) ?? [];
        return new static($_GET, $_POST, $json, $_FILES, $_COOKIE, $_SERVER);
    }

    private function filter(string $request, bool $array = false): object|array
    {
        if (!property_exists($this, $request))
            throw new \Exception("Property {$request} not exist!");

        $filter = filter_var_array(array_map(function ($value) {
            if (is_array($value)) {
                return array_map(function ($value) {
                    return trim(strip_tags($value));
                }, $value);
            }

            return trim(strip_tags($value));
        }, $this->$request));

        return $array ? $filter : (object) $filter;
    }

    public function get(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter("get", $array);

        return $array ? $this->get : (object) $this->get;
    }
    public function post(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter("post", $array);

        return $array ? $this->post : (object) $this->post;
    }
    public function files(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter("files", $array);

        return $array ? $this->files : (object) $this->files;
    }
    public function cookies(bool $filter = true, bool $array = true): object|array
    {
        if ($filter)
            return $this->filter("cookies", $array);

        return $array ? $this->cookies : (object) $this->cookies;
    }
    public function server(bool $filter = true, bool $array = true): object|array
    {
        if ($filter)
            return $this->filter("server", $array);

        return $array ? $this->server : (object) $this->server;
    }

    public function json(bool $filter = true, bool $array = false): object|array
    {
        if ($filter)
            return $this->filter("json", $array);

        return $array ? $this->json : (object) $this->json;
    }
}
