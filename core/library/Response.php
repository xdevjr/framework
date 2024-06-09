<?php

namespace core\library;

class Response
{

    /**
     * @param array $headers example: ["Content-Type" => "application/json"]
     * @param bool $json if set true the body will be json encoded and the content type header will be set
     */
    public function __construct(
        private mixed $body,
        private int $statusCode = 200,
        private array $headers = [],
        private bool $json = false
    ) {
        $this->json ? $this->json() : $this->send();
    }

    private function getHeaders(): void
    {
        if (!empty($this->headers))
            foreach ($this->headers as $index => $header)
                header("$index:$header");

        return;
    }

    private function send(): void
    {
        if (!is_string($this->body))
            throw new \Exception("The response body is not a valid string. Recommended to set json to true.");

        http_response_code($this->statusCode);
        $this->getHeaders();

        echo $this->body;
    }

    private function json(): void
    {
        if (!json_validate(json_encode($this->body)))
            throw new \Exception("The body of the response is not valid JSON.");

        http_response_code($this->statusCode);
        header("Content-Type: application/json");
        $this->getHeaders();

        echo json_encode($this->body);
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
    }

}
