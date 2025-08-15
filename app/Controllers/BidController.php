<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\BidService;

final class BidController
{
    use \App\Core\AuthHelper;

    public function store(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/auctions');

        $auctionId = (int)($data['auction_id'] ?? 0);
        $price = (float)($data['price'] ?? 0);

        if ($auctionId <= 0 || $price <= 0) {
            $this->redirectWithError('Offre invalide.', '/auctions');
        }

        $userId = (int)$_SESSION['user']['id'];
        $ok = (new BidService())->placeBid($auctionId, $userId, $price);

        $redirectUrl = "/auction/show?id=$auctionId";
        if ($ok) {
            $this->redirectWithSuccess('Offre placée.', $redirectUrl);
        } else {
            $this->redirectWithError('Offre refusée (règles).', $redirectUrl);
        }
    }

    public function delete(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/auctions');

        $bidId = (int)($data['id'] ?? 0);
        $auctionId = (int)($data['auction_id'] ?? 0);

        if ($bidId <= 0 || $auctionId <= 0) {
            View::redirect('/auctions');
        }

        $userId = (int)$_SESSION['user']['id'];
        $ok = (new BidService())->delete($bidId, $userId);

        $redirectUrl = "/auction/show?id=$auctionId";
        if ($ok) {
            $this->redirectWithSuccess('Offre supprimée.', $redirectUrl);
        } else {
            $this->redirectWithError('Suppression refusée.', $redirectUrl);
        }
    }
}
