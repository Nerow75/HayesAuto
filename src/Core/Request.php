<?php

namespace App\Core;

class Request
{
    public function get(string $key, $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function server(string $key, $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function allPost(): array
    {
        return $_POST;
    }

    public function allGet(): array
    {
        return $_GET;
    }
}
