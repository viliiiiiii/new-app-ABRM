<?php
namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function any(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $path, string $method)
    {
        $method = strtoupper($method);
        $path = rtrim($path, '/') ?: '/';
        if (isset($this->routes[$method][$path])) {
            return call_user_func($this->routes[$method][$path]);
        }
        http_response_code(404);
        echo '404 - Not Found';
        return null;
    }
}
