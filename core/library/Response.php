<?php

namespace core\library;

class Response
{
    public function __construct(
        private mixed $body = null,
        private int $statusCode = 200,
        private array $headers = []
    ) {
    }

    public static function create(mixed $body = null, int $statusCode = 200, array $headers = []): Response
    {
        return new static($body, $statusCode, $headers);
    }

    private function getHeaders(): void
    {
        if (!empty($this->headers)) {
            foreach ($this->headers as $index => $header) {
                header("$index:$header");
            }
        }
        return;
    }

    public function send(bool $json = false): mixed
    {
        http_response_code($this->statusCode);
        $json ? header("Content-Type: application/json") : null;
        $this->getHeaders();

        return $json ? json_encode($this->body) : $this->body;
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
    }

}
