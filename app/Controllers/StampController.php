<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\CsrfToken;
use App\Services\StampService;
use App\Services\CountryService;

final class StampController
{
    private StampService $stampService;

    public function __construct()
    {
        $this->stampService = new StampService();
    }

    public function index(): void
    {
        $stamps = $this->stampService->listWithCountryAndMainImage();
        View::render('pages/stamp/index', ['stamps' => $stamps]);
    }

    public function show(array $data): void
    {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/stamps');
        }
        $stamp = $this->stampService->getByIdFull($id);
        if (!$stamp) {
            $_SESSION['flash']['error'] = 'Timbre introuvable.';
            View::redirect('/stamps');
        }
        View::render('pages/stamp/show', ['stamp' => $stamp]);
    }

    public function create(): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        $countries = (new CountryService())->listAll();
        View::render('pages/stamp/create', ['countries' => $countries]);
    }

    public function store(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/stamp/create');
        }

        $ok = $this->stampService->createFromForm($data);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Timbre créé.' : 'Création refusée.';
        View::redirect('/stamps');
    }

    public function edit(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/stamps');
        }
        $stamp = $this->stampService->getByIdFull($id);
        if (!$stamp) {
            $_SESSION['flash']['error'] = 'Timbre introuvable.';
            View::redirect('/stamps');
        }
        $countries = (new CountryService())->listAll();
        View::render('pages/stamp/edit', ['stamp' => $stamp, 'countries' => $countries]);
    }

    public function update(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            View::redirect('/stamps');
        }
        $ok = $this->stampService->updateFromForm($data);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Timbre mis à jour.' : 'Mise à jour refusée.';
        View::redirect('/stamps');
    }

    public function delete(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            View::redirect('/stamps');
        }
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/stamps');
        }
        $ok = $this->stampService->delete($id);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Timbre supprimé.' : 'Suppression refusée (liens actifs).';
        View::redirect('/stamps');
    }
}
