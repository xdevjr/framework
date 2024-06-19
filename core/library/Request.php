<?php

namespace core\library;

readonly class Request
{
    public function __construct(
        private array $get,
        private array $post,
        private array $json,
        private array $files,
        private array $cookie,
        private array $server
    ) {
    }

    public static function create(): static
    {
        $input = file_get_contents('php://input');
        $_JSON = json_validate($input) ? json_decode($input, true) : [];
        return new static($_GET, $_POST, $_JSON, $_FILES, $_COOKIE, $_SERVER);
    }

    private function filter(array $request, bool $object = false): object|array
    {
        $filter = filter_var_array(array_map(function ($value) {
            if (is_array($value)) {
                return array_map(function ($value) {
                    return trim(strip_tags($value));
                }, $value);
            }

            return trim(strip_tags($value));
        }, $request));

        return $object ? (object) $filter : $filter;
    }

    public function all(bool $filter = true, bool $object = false): object|array
    {
        $request = [...$this->get, ...$this->post, ...$this->json, ...$this->files];
        return $filter ? $this->filter($request, $object) : ($object ? (object) $request : $request);
    }

    public function only(string $request, bool $filter = true, bool $object = false): object|array
    {
        if (!property_exists($this, $request))
            throw new \Exception("Request {$request} does not exist!");

        return $filter ? $this->filter($this->$request, $object) : ($object ? (object) $this->$request : $this->$request);
    }

    public function input(string $key, mixed $default = null, bool $filter = true): mixed
    {
        return $this->all($filter)[$key] ?? $default;
    }
}
