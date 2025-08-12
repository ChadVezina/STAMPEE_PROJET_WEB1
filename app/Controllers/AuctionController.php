<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\CsrfToken;
use App\Services\AuctionService;
use App\Services\BidService;
use App\Services\StampService;
use App\Services\FavoriteService;

final class AuctionController
{
    private AuctionService $auctionService;

    public function __construct()
    {
        $this->auctionService = new AuctionService();
    }

    public function index(): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        $auctions = $this->auctionService->getAllWithMeta($userId);
        View::render('pages/auction/index', ['auctions' => $auctions]);
    }

    public function show(array $data): void
    {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if ($id <= 0) {
            $_SESSION['flash']['error'] = 'Enchère introuvable.';
            View::redirect('/auctions');
        }

        $userId = $_SESSION['user']['id'] ?? null;
        $auction = $this->auctionService->getByIdWithMeta($id, $userId);
        if (!$auction) {
            $_SESSION['flash']['error'] = 'Enchère introuvable.';
            View::redirect('/auctions');
        }

        $bids = (new BidService())->getByAuction($id);
        View::render('pages/auction/show', [
            'auction' => $auction,
            'bids'    => $bids,
        ]);
    }

    public function create(): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        $stamps = (new StampService())->getAllBasic();
        View::render('pages/auction/create', ['stamps' => $stamps]);
    }

    public function store(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/auction/create');
        }

        $stampId = (int)($data['stamp_id'] ?? 0);
        $minPrice = (float)($data['min_price'] ?? 0);
        $start = trim($data['auction_start'] ?? '');
        $end   = trim($data['auction_end'] ?? '');
        $favorite = (isset($data['favorite']) && $data['favorite'] === '1');

        if ($stampId <= 0 || $minPrice <= 0 || $start === '' || $end === '') {
            $_SESSION['flash']['error'] = 'Champs requis manquants/invalides.';
            View::redirect('/auction/create');
        }

        $sellerId = (int)$_SESSION['user']['id'];
        $newId = $this->auctionService->create($stampId, $sellerId, $start, $end, $minPrice, $favorite);
        if (!$newId) {
            $_SESSION['flash']['error'] = 'Création échouée (dates/prix).';
            View::redirect('/auction/create');
        }

        $_SESSION['flash']['success'] = 'Enchère créée.';
        View::redirect('/auction/show?id=' . $newId);
    }

    public function edit(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/auctions');
        }

        $auction = $this->auctionService->getById($id);
        if (!$auction || (int)$auction['seller_id'] !== (int)$_SESSION['user']['id']) {
            $_SESSION['flash']['error'] = 'Accès refusé.';
            View::redirect('/auctions');
        }

        $stamps = (new StampService())->getAllBasic();
        View::render('pages/auction/edit', [
            'auction' => $auction,
            'stamps'  => $stamps,
        ]);
    }

    public function update(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/auctions');
        }

        $id = (int)($data['id'] ?? 0);
        $stampId = (int)($data['stamp_id'] ?? 0);
        $minPrice = (float)($data['min_price'] ?? 0);
        $start = trim($data['auction_start'] ?? '');
        $end   = trim($data['auction_end'] ?? '');
        $favorite = (isset($data['favorite']) && $data['favorite'] === '1');

        if ($id <= 0 || $stampId <= 0 || $minPrice <= 0 || $start === '' || $end === '') {
            $_SESSION['flash']['error'] = 'Données invalides.';
            View::redirect('/auctions');
        }

        $ok = $this->auctionService->update($id, (int)$_SESSION['user']['id'], $stampId, $start, $end, $minPrice, $favorite);
        if (!$ok) {
            $_SESSION['flash']['error'] = 'Mise à jour refusée/échouée.';
            View::redirect('/auction/edit?id=' . $id);
        }

        $_SESSION['flash']['success'] = 'Enchère mise à jour.';
        View::redirect('/auction/show?id=' . $id);
    }

    public function delete(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            View::redirect('/auctions');
        }

        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/auctions');
        }

        $ok = $this->auctionService->delete($id, (int)$_SESSION['user']['id']);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Enchère supprimée.' : 'Suppression refusée.';
        View::redirect('/auctions');
    }
}
