<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\CsrfToken;
use App\Services\BidService;

final class BidController
{
    public function store(array $data): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash']['error'] = 'Connexion requise.';
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            $_SESSION['flash']['error'] = 'CSRF invalide.';
            $aid = (int)($data['auction_id'] ?? 0);
            View::redirect($aid > 0 ? "/auction/show?id=$aid" : '/auctions');
        }

        $auctionId = (int)($data['auction_id'] ?? 0);
        $price = (float)($data['price'] ?? 0);
        if ($auctionId <= 0 || $price <= 0) {
            $_SESSION['flash']['error'] = 'Offre invalide.';
            View::redirect('/auctions');
        }

        $ok = (new BidService())->placeBid($auctionId, (int)$_SESSION['user']['id'], $price);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Offre placée.' : 'Offre refusée (règles).';
        View::redirect("/auction/show?id=$auctionId");
    }

    public function delete(array $data): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        if (!CsrfToken::check($data['_token'] ?? null)) {
            View::redirect('/auctions');
        }
        $bidId = (int)($data['id'] ?? 0);
        $auctionId = (int)($data['auction_id'] ?? 0);
        if ($bidId <= 0 || $auctionId <= 0) {
            View::redirect('/auctions');
        }

        $ok = (new BidService())->delete($bidId, (int)$_SESSION['user']['id']);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Offre supprimée.' : 'Suppression refusée.';
        View::redirect("/auction/show?id=$auctionId");
    }
}
