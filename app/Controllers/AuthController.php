<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\CsrfToken;
use App\Models\User;

final class AuthController
{
    public function showLogin(): void
    {
        View::render('pages/auth/login', []);
    }

    public function login(array $data): void
    {
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'Jeton CSRF invalide.';
            View::redirect('/login');
        }

        $email = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $_SESSION['flash']['error'] = 'Email ou mot de passe invalide.';
            View::redirect('/login');
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash']['error'] = 'Identifiants incorrects.';
            View::redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'nom'   => $user['nom'],
            'email' => $user['email'],
        ];
        View::redirect('/dashboard');
    }

    public function showRegister(): void
    {
        View::render('pages/auth/register', []);
    }

    public function register(array $data): void
    {
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'Jeton CSRF invalide.';
            View::redirect('/register');
        }

        $nom = trim($data['nom'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');
        $confirm = (string)($data['confirm'] ?? '');

        if ($nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash']['error'] = 'Champs requis manquants.';
            View::redirect('/register');
        }
        if (strlen($password) < 8) {
            $_SESSION['flash']['error'] = 'Mot de passe: 8 caractères min.';
            View::redirect('/register');
        }
        if ($password !== $confirm) {
            $_SESSION['flash']['error'] = 'Les mots de passe ne correspondent pas.';
            View::redirect('/register');
        }
        if (User::findByEmail($email)) {
            $_SESSION['flash']['error'] = 'Email déjà utilisé.';
            View::redirect('/register');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (!User::create($nom, $email, $hash)) {
            $_SESSION['flash']['error'] = 'Erreur à la création.';
            View::redirect('/register');
        }

        $_SESSION['flash']['success'] = 'Inscription réussie.';
        View::redirect('/login');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        View::redirect('/login');
    }
}
