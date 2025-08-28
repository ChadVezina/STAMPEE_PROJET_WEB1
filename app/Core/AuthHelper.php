<?php

declare(strict_types=1);

namespace App\Core;

trait AuthHelper
{
    /**
     * Check if user is authenticated, redirect to login if not
     */
    protected function requireAuth(): array
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        return $_SESSION['user'];
    }

    /**
     * Check if user is logged in (without redirect)
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user']);
    }

    /**
     * Check if logged in user is super admin
     */
    public static function isSuperAdmin(): bool
    {
        return !empty($_SESSION['user']) &&
            isset($_SESSION['user']['role']) &&
            $_SESSION['user']['role'] === 'super_admin';
    }

    /**
     * Check if logged in user is admin or super admin
     */
    public static function isAdmin(): bool
    {
        return !empty($_SESSION['user']) &&
            isset($_SESSION['user']['role']) &&
            in_array($_SESSION['user']['role'], ['admin', 'super_admin']);
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Set error message and redirect
     */
    protected function redirectWithError(string $message, string $path): void
    {
        $_SESSION['flash']['error'] = $message;
        View::redirect($path);
    }

    /**
     * Set success message and redirect
     */
    protected function redirectWithSuccess(string $message, string $path): void
    {
        $_SESSION['flash']['success'] = $message;
        View::redirect($path);
    }

    /**
     * Validate CSRF token, redirect with error if invalid
     */
    protected function validateCsrfOrRedirect(?string $token, string $redirectPath): void
    {
        if (!CsrfToken::check($token)) {
            $this->redirectWithError('CSRF invalide.', $redirectPath);
        }
    }

    /**
     * Validate required fields are not empty
     */
    protected function validateRequired(array $data, array $fields): bool
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}
