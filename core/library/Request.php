<?php

namespace core\library;

class Request
{
    public function __construct(
        public array $data
    ) {
    }

    public static function all(): static
    {
        $data = filter_var_array($GLOBALS);
        return new static($GLOBALS);
    }

    public function __get(string $key): array
    {
        $key = "_" . strtoupper($key);
        $data = array_map('strip_tags', array_map('trim', $this->data[$key]));
        return $data;
    }
}
