<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\CsrfToken;
use App\Models\User;
use App\Services\CountryService;
use App\Services\StampService;
use App\Core\DB;

final class DashboardController
{
    use \App\Core\AuthHelper;

    public function index(array $data = []): void
    {
        $this->requireAuth();

        $mode = $data['mode'] ?? $_GET['mode'] ?? 'default';
        $user = $_SESSION['user'];

        // Redirect deprecated add-stamp mode to the official stamps/create route
        if ($mode === 'add-stamp') {
            View::redirect('/stamps/create');
            return;
        }

        View::render('pages/dashboard/index', [
            'user' => $user,
            'mode' => $mode
        ]);
    }

    public function emailForm(): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        View::render('pages/dashboard/email', ['user' => $_SESSION['user']]);
    }

    public function updateEmail(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/dashboard/email');
        }

        $email  = trim($data['email'] ?? '');
        $confirm = trim($data['confirm_email'] ?? '');
        $pwd    = (string)($data['current_password'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $email !== $confirm) {
            $_SESSION['flash']['error'] = 'Email invalide ou non confirmé.';
            View::redirect('/dashboard/email');
        }

        $user = User::findById((int)$_SESSION['user']['id']);
        if (!$user || !password_verify($pwd, $user['password'])) {
            $_SESSION['flash']['error'] = 'Mot de passe actuel incorrect.';
            View::redirect('/dashboard/email');
        }

        if (!User::updateEmail((int)$user['id'], $email)) {
            $_SESSION['flash']['error'] = 'Mise à jour email refusée.';
            View::redirect('/dashboard/email');
        }
        $_SESSION['user']['email'] = $email;
        $_SESSION['flash']['success'] = 'Email mis à jour.';
        View::redirect('/dashboard/email');
    }
    public function passwordForm(): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        View::render('pages/dashboard/password', []);
    }

    public function updatePassword(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/dashboard/password');
        }
        $current = (string)($data['current_password'] ?? '');
        $new     = (string)($data['new_password'] ?? '');
        $confirm = (string)($data['confirm_password'] ?? '');

        if (strlen($new) < 8 || $new !== $confirm) {
            $_SESSION['flash']['error'] = 'Nouveau mot de passe invalide ou non confirmé.';
            View::redirect('/dashboard/password');
        }

        $user = User::findById((int)$_SESSION['user']['id']);
        if (!$user || !password_verify($current, $user['password'])) {
            $_SESSION['flash']['error'] = 'Mot de passe actuel incorrect.';
            View::redirect('/dashboard/password');
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        if (!User::updatePassword((int)$user['id'], $hash)) {
            $_SESSION['flash']['error'] = 'Mise à jour refusée.';
            View::redirect('/dashboard/password');
        }
        $_SESSION['flash']['success'] = 'Mot de passe mis à jour.';
        View::redirect('/dashboard/password');
    }
    public function deleteForm(): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        View::render('pages/dashboard/delete', []);
    }

    public function deleteAccount(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/dashboard/delete');
        }

        $pwd = (string)($data['current_password'] ?? '');
        $phrase = strtoupper(trim((string)($data['confirm_phrase'] ?? '')));
        if ($phrase !== 'SUPPRIMER') {
            $_SESSION['flash']['error'] = 'Phrase de confirmation invalide.';
            View::redirect('/dashboard/delete');
        }

        $user = User::findById((int)$_SESSION['user']['id']);
        if (!$user || !password_verify($pwd, $user['password'])) {
            $_SESSION['flash']['error'] = 'Mot de passe actuel incorrect.';
            View::redirect('/dashboard/delete');
        }

        if (!User::deleteById((int)$user['id'])) {
            $_SESSION['flash']['error'] = 'Suppression refusée (liens actifs ?).';
            View::redirect('/dashboard/delete');
        }

        // Logout propre
        $_SESSION = [];
        session_destroy();
        View::redirect('/'); // retour accueil
    }
}
