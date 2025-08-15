<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\FavoriteService;
use App\Services\AuctionService;

final class PublicController
{
    use \App\Core\AuthHelper;

    public function home(): void
    {
        $fav = new FavoriteService();
        $au  = new AuctionService();

        $featured = $fav->listFeatured(10);            // "Coup de Cœur du Lord"
        $running  = $au->getActivePaginated(1, 8)['items']; // petites cartes "Offres en cours"

        View::render('pages/accueil/home-page', [
            'featured' => $featured,
            'running'  => $running,
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
