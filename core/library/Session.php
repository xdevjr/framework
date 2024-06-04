<?php

namespace core\library;

class Session
{
    public function all(): array
    {
        return $_SESSION;
    }

    public function get(string $key): mixed
    {
        return $this->has($key) ? $_SESSION[$key] : null;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        if ($this->has($key))
            unset($_SESSION[$key]);
    }

    public function removeAll(): void
    {
        session_destroy();
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['__flash'][$key] = $value;
    }

    public function flashArray(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->flash($key, $value);
        }
    }

    public function flashRemove(string $key = null): void
    {
        if ($key) {
            if ($this->hasFlash($key))
                unset($_SESSION['__flash'][$key]);

            if (empty($this->getFlash()))
                unset($_SESSION['__flash']);

            return;
        }
        unset($_SESSION['__flash']);
    }

    public function getFlash(string $key = null): mixed
    {
        if ($key)
            return $this->get('__flash')[$key] ?? null;

        return $this->get('__flash') ?? null;
    }

    public function hasFlash(string $key): bool
    {
        return $this->has('__flash') && $this->getFlash($key) !== null;
    }

    public function createCsrfToken(): void
    {
        if (!isset($_SESSION['__csrf']) or $_SERVER['REQUEST_METHOD'] === 'GET')
            $_SESSION['__csrf'] = bin2hex(random_bytes(32));
    }

    public function checkCsrfToken(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' and (!isset($_POST['__csrf']) or !hash_equals($_SESSION['__csrf'], $_POST['__csrf'])))
            throw new \Exception("Error processing request, csrf token is not valid!");
    }

    public function getCsrfToken(): string
    {
        return $_SESSION['__csrf'];
    }

}
