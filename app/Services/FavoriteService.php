<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;

final class FavoriteService
{
    /**
     * Liste des enchères “Choix du site” issues de la table Favorite
     * (entries gérées en base), avec méta utile pour un carrousel/bandeau.
     * @return array<int, array<string,mixed>>
     */
    public function listFeatured(int $limit = 12): array
    {
        $sql = "
            SELECT a.*,
                   s.name AS stamp_name,
                   u.nom  AS seller_name,
                   (SELECT MAX(b.price) FROM `Bid` b WHERE b.auction_id = a.id) AS current_price
            FROM `Favorite` f
            JOIN `Auction` a ON a.id = f.auction_id
            JOIN `Stamp`   s ON s.id = a.stamp_id
            JOIN `User`    u ON u.id = a.seller_id
            ORDER BY f.favorite_at DESC, a.auction_start DESC
            LIMIT :lim
        ";
        $stmt = DB::pdo()->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        // Marqueur visuel homogène avec AuctionService
        foreach ($rows as &$r) {
            $r['is_featured'] = true;
        }
        return $rows;
    }

    /**
     * Vérifie si une enchère est “mise en avant” (Favorite ou flag Auction.favorite).
     */
    public function isFeatured(int $auctionId): bool
    {
        $stmt = DB::pdo()->prepare("
            SELECT
              EXISTS (SELECT 1 FROM `Favorite` f WHERE f.auction_id = :id)
              OR EXISTS (SELECT 1 FROM `Auction` a WHERE a.id = :id AND a.favorite = 1)
        ");
        $stmt->execute([':id' => $auctionId]);
        return (bool)$stmt->fetchColumn();
    }
}
