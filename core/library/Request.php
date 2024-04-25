<?php

namespace core\library;

class Request
{
    public function __construct(
        private array $get,
        private array $post,
        private array $files,
        private array $cookies,
        private array $server
    ) {
    }

    public static function all(): static
    {
        return new static($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);
    }

    public function __get(string $key): array
    {
        return array_map('strip_tags', array_map('trim', $this->$key));
    }
}
