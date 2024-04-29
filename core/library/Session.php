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

    public function flashRemove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $this->has('__flash'))
            unset($_SESSION['__flash']);
    }

    public function getFlash(string $key = null): mixed
    {
        if ($key != null)
            return $this->get('__flash')[$key] ?? null;

        return $this->get('__flash') ?? null;
    }

}
