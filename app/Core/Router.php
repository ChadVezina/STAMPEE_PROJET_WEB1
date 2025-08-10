<?php

namespace App\Core;

final class Router
{
    private static array $routes = ['GET' => [], 'POST' => []];

    public static function get(string $path, callable|array $handler): void
    {
        self::$routes['GET'][$path] = $handler;
    }
    public static function post(string $path, callable|array $handler): void
    {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Remove /stampee prefix if present
        if (strpos($uri, '/stampee') === 0) {
            $uri = substr($uri, 8); // Remove '/stampee'
        }

        // Clean the path
        $path = rtrim($uri, '/') ?: '/';

        $handler = self::$routes[$method][$path] ?? null;
        if (!$handler) {
            http_response_code(404);
            echo "404 - Path not found: $path (original URI: " . $_SERVER['REQUEST_URI'] . ")";
            return;
        }
        if (is_array($handler)) {
            [$class, $action] = $handler;
            $instance = new $class();
            $instance->$action();
            return;
        }
        $handler();
    }
}
