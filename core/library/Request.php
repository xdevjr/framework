<?php

namespace core\library;

readonly class Request
{
    public function __construct(
        public array $get,
        public array $post,
        public array $json,
        public array $files,
        public array $cookie,
        public array $server
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

    public function only(string|array $requests, bool $filter = false, bool $object = false): object|array
    {
        if (is_array($requests)) {
            $requests = array_reduce($requests, function ($carry, $request) {
                if (!property_exists($this, $request))
                    throw new \Exception("Request {$request} does not exist!");

                $carry[$request] = $this->$request;
                return $carry;
            }, []);
        } else {
            if (!property_exists($this, $requests))
                throw new \Exception("Request {$requests} does not exist!");

            $requests = $this->$requests;
        }

        return $filter ? $this->filter($requests, $object) : ($object ? (object) $requests : $requests);
    }

    public function input(string $key, mixed $default = null, bool $filter = true): mixed
    {
        return $this->all($filter)[$key] ?? $default;
    }
}
