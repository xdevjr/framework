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

    private function filterRequest(): void
    {
        $this->get = filter_var_array(array_map('strip_tags', array_map('trim', $this->get)));
        $this->post = filter_var_array(array_map('strip_tags', array_map('trim', $this->post)));
        $this->files = filter_var_array(array_map('strip_tags', array_map('trim', $this->files)));
    }

    public function __call($property, $key): mixed
    {
        $this->filterRequest();

        if (property_exists($this, $property) and empty($key))
            return (object) $this->$property;
        elseif (property_exists($this, $property) and count($key) == 1)
            return $this->$property[$key[0]];

        return "Error: Method not exists or you passed more than one property.";
    }
}
