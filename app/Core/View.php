<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        // Use BASE_PATH if already defined, otherwise define it
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 2));
        }
        if (!defined('VIEW_PATH')) {
            define('VIEW_PATH', BASE_PATH . '/ressources/views');
        }

        extract($data, EXTR_SKIP);
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        ob_start();
        require VIEW_PATH . '/' . $view . '.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/app.php';
    }
    public static function redirect(string $to): void
    {
        $basePath = Config::get('app.base_path', '');
        if ($basePath && str_starts_with($to, '/') && !str_starts_with($to, "$basePath/")) {
            $to = $basePath . $to;
        }
        header("Location: $to");
        exit;
    }
}
