<?php
namespace App\Core;

final class CsrfToken {
    private const KEY='csrf_token';

    public static function boot(): void {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
    }
    public static function token(): string { return $_SESSION[self::KEY]; }
    public static function check(?string $token): bool {
        return is_string($token) && hash_equals($_SESSION[self::KEY] ?? '', $token);
    }
    public static function field(): string {
        return '<input type="hidden" name="_token" value="'.htmlspecialchars(self::token(), ENT_QUOTES).'">';
    }
}