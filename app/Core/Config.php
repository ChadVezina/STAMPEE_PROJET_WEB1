<?php
namespace App\Core;

final class Config {
    private static array $store = [];

    public static function set(string $key, mixed $value): void {
        self::$store[$key] = $value;
    }
    public static function get(string $key, mixed $default=null): mixed {
        return self::$store[$key] ?? $default;
    }
}