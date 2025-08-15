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
