<?php

namespace core\library;

class Session
{

    public static function all()
    {
        return $_SESSION;
    }

    public static function get(string $key): mixed
    {
        return self::has($key) ? $_SESSION[$key] : null;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        if (self::has($key))
            unset($_SESSION[$key]);
    }

    public static function removeAll(): void
    {
        session_destroy();
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['__flash'][$key] = $value;
    }

    public static function flashRemove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && self::has('__flash')) {
            unset($_SESSION['__flash']);
        }
    }

    public static function getFlash(): mixed
    {
        return self::get('__flash');
    }

}
