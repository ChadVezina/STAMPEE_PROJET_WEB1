<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\CsrfToken;
use App\Services\AuctionService;
use App\Services\BidService;
use App\Services\StampService;

final class AuctionController
{
    use \App\Core\AuthHelper;

    private AuctionService $auctionService;

    public function __construct()
    {
        $this->auctionService = new AuctionService();
    }

    public function index(): void
    {
        $auctions = $this->auctionService->getAllWithMeta();
        View::render('pages/auction/index', ['auctions' => $auctions]);
    }

    public function show(array $data): void
    {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if ($id <= 0) {
            $this->redirectWithError('Enchère introuvable.', '/auctions');
        }

        $auction = $this->auctionService->getByIdWithMeta($id);
        if (!$auction) {
            $this->redirectWithError('Enchère introuvable.', '/auctions');
        }

        $bids = (new BidService())->getByAuction($id);
        View::render('pages/auction/show', [
            'auction' => $auction,
            'bids'    => $bids,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $stamps = (new StampService())->getAllBasic();
        View::render('pages/auction/create', ['stamps' => $stamps]);
    }

    public function store(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/auctions/create');

        $stampId = (int)($data['stamp_id'] ?? 0);
        $minPrice = (float)($data['min_price'] ?? 0);
        $start = trim($data['auction_start'] ?? '');
        $end   = trim($data['auction_end'] ?? '');
        $favorite = (isset($data['favorite']) && $data['favorite'] === '1');

        if ($stampId <= 0 || $minPrice <= 0 || $start === '' || $end === '') {
            $this->redirectWithError('Champs requis manquants/invalides.', '/auctions/create');
        }

        $sellerId = (int)$_SESSION['user']['id'];
        $newId = $this->auctionService->create($stampId, $sellerId, $start, $end, $minPrice, $favorite);
        if (!$newId) {
            $this->redirectWithError('Création échouée (dates/prix).', '/auctions/create');
        }

        $this->redirectWithSuccess('Enchère créée.', '/auctions/show?id=' . $newId);
    }

    public function edit(array $data): void
    {
        $this->requireAuth();
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/auctions');
        }

        $auction = $this->auctionService->getById($id);
        if (!$auction || (int)$auction['seller_id'] !== (int)$_SESSION['user']['id']) {
            $this->redirectWithError('Accès refusé.', '/auctions');
        }

        $stamps = (new StampService())->getAllBasic();
        View::render('pages/auction/edit', [
            'auction' => $auction,
            'stamps'  => $stamps,
        ]);
    }

    public function update(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/auctions');

        $id = (int)($data['id'] ?? 0);
        $stampId = (int)($data['stamp_id'] ?? 0);
        $minPrice = (float)($data['min_price'] ?? 0);
        $start = trim($data['auction_start'] ?? '');
        $end   = trim($data['auction_end'] ?? '');
        $favorite = (isset($data['favorite']) && $data['favorite'] === '1');

        if ($id <= 0 || $stampId <= 0 || $minPrice <= 0 || $start === '' || $end === '') {
            $this->redirectWithError('Données invalides.', '/auctions');
        }

        $ok = $this->auctionService->update($id, (int)$_SESSION['user']['id'], $stampId, $start, $end, $minPrice, $favorite);
        if (!$ok) {
            $this->redirectWithError('Mise à jour refusée/échouée.', '/auction/edit?id=' . $id);
        }

        $this->redirectWithSuccess('Enchère mise à jour.', '/auctions/show?id=' . $id);
        View::redirect('/auctions/show?id=' . $id);
    }

    public function delete(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/auctions');

        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/auctions');
        }

        $ok = $this->auctionService->delete($id, (int)$_SESSION['user']['id']);
        $message = $ok ? 'Enchère supprimée.' : 'Suppression refusée.';

        if ($ok) {
            $this->redirectWithSuccess($message, '/auctions');
        } else {
            $this->redirectWithError($message, '/auctions');
        }
    }

    public function publicIndex(array $data = []): void
    {
        $page    = max(1, (int)($data['page'] ?? 1));
        $perPage = 9;

        $srv  = new AuctionService();
        $resp = $srv->getActivePaginated($page, $perPage);

        View::render('pages/auction/index', [
            'auctions'  => $resp['items'],
            'page'      => $resp['page'],
            'pages'     => $resp['pages'],
            'total'     => $resp['total'],
            'perPage'   => $perPage,
        ]);
    }
}
