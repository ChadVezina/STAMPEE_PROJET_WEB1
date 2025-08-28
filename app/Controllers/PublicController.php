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

        // Get auction favorites (existing system)
        $auctionFeatured = $fav->listFeatured(6);

        // Get stamp favorites (new system) - temporarily disabled until DB structure is clarified
        $stampFavorites = []; // $this->getStampFavorites(6);

        // Combine both for a total of 12 items max
        $featured = array_merge($auctionFeatured, $stampFavorites);

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

    /**
     * Get stamp favorites for the home page
     */
    private function getStampFavorites(int $limit = 6): array
    {
        $sql = "
            SELECT f.id as favorite_id, f.stamp_id, f.created_at as favorite_date,
                   s.name as stamp_name, s.country_code, s.user_id,
                   c.name_fr AS country_name,
                   u.nom as seller_name,
                   si.url AS main_image,
                   'stamp_favorite' as type
            FROM `Favorite` f
            INNER JOIN `Stamp` s ON f.stamp_id = s.id
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = s.user_id
            LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
            ORDER BY f.created_at DESC
            LIMIT :limit
        ";

        $stmt = \App\Core\DB::pdo()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $favorites = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Format for compatibility with home page template
        foreach ($favorites as &$fav) {
            $fav['is_featured'] = true;
            $fav['current_price'] = null; // Stamps don't have auction prices
            $fav['min_price'] = null;
            $fav['auction_end'] = null; // Stamps don't have auction end times
        }

        return $favorites;
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        View::render('pages/accueil/dashboard', [
            'user' => $_SESSION['user']
        ]);
    }
}
