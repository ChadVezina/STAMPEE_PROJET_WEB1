<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\FavoriteService;
use App\Services\AuctionService;
use App\Models\Bid;

final class PublicController
{
    use \App\Core\AuthHelper;

    public function home(): void
    {
        $fav = new FavoriteService();
        $au  = new AuctionService();

        $featured = $fav->listFeatured(10);            // "Coup de Cœur du Lord"
        $running  = $au->getActivePaginated(1, 8)['items']; // petites cartes "Offres en cours"
        $recentBids = Bid::findRecentBids(5); // Recent bids for "Actualités" section

        // Enrichir chaque enchère avec le dernier enchérisseur (nom + date) si présent
        foreach ($running as &$r) {
            $bid = Bid::findHighestByAuction((int)($r['id'] ?? 0));
            $r['last_bid_user'] = $bid['nom'] ?? null;
            $r['last_bid_time'] = isset($bid['bid_at']) ? date('c', strtotime($bid['bid_at'])) : null;
        }
        unset($r);

        View::render('pages/accueil/home-page', [
            'featured' => $featured,
            'running'  => $running,
            'recentBids' => $recentBids,
        ]);
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        View::render('pages/accueil/dashboard', [
            'user' => $_SESSION['user']
        ]);
    }
}
