<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\CsrfToken;
use App\Core\View;
use App\Services\AuctionService;
use App\Services\BidService;
use App\Services\StampService;
use App\Services\CountryService;

final class StampController
{
    use \App\Core\AuthHelper;

    private StampService $stampService;

    public function __construct()
    {
        $this->stampService = new StampService();
    }

    public function index(): void
    {
        $stamps = $this->stampService->listWithCountryAndMainImage();
        View::render('pages/stamps/index', ['stamps' => $stamps]);
    }

    public function show(array $data): void
    {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/stamps');
        }
        $stamp = $this->stampService->getByIdFull($id);
        if (!$stamp) {
            $this->redirectWithError('Timbre introuvable.', '/stamps');
        }
        View::render('pages/stamps/show', ['stamp' => $stamp]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $countries = (new CountryService())->listAll();
        View::render('pages/stamps/create', ['countries' => $countries]);
    }

    public function store(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/stamps/create');
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
        View::render('pages/stamps/edit', ['stamp' => $stamp, 'countries' => $countries]);
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

    public function publicShow(array $data): void
    {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/auctions');
        }

        $stSrv = new StampService();
        $auSrv = new AuctionService();

        $stamp = $stSrv->getByIdFull($id);
        if (!$stamp) {
            $_SESSION['flash']['error'] = 'Timbre introuvable.';
            View::redirect('/auctions');
        }

        // Optionnel: enchère active liée au timbre
        $auction = $auSrv->getActiveByStamp($id);

        // Offres (si besoin d’afficher historique)
        $bids = [];
        if ($auction) {
            $bids = (new BidService())->getByAuction((int)$auction['id']);
        }

        View::render('pages/stamps/index', [
            'stamp'   => $stamp,
            'auction' => $auction,
            'bids'    => $bids
        ]);
    }
}
