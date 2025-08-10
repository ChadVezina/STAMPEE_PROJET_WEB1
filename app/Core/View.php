<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
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
        $basePath = '/stampee';
        if (strpos($to, '/') === 0 && strpos($to, $basePath) !== 0) {
            $to = $basePath . $to;
        }
        header('Location: ' . $to);
        exit;
    }
}
