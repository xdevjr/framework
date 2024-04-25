<?php

namespace core\library;

class Response
{
    public function __construct(
        private mixed $body,
        private int $statusCode = 200,
        private array $headers = []
    ) {
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

    public function send(bool $json = false): void
    {
        http_response_code($this->statusCode);
        $json ? header("Content-Type: application/json") : null;
        $this->getHeaders();

        echo $json ? json_encode($this->body) : $this->body;
    }

    public function redirect(string $url, bool $json = false): void
    {
        if (!empty($this->body))
            $_SESSION['data'] = $json ? json_encode($this->body) : $this->body;

        header("Location: $url", true, $this->statusCode);
    }

}
