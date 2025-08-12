<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;

final class BidService
{
    public function getByAuction(int $auctionId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT b.id, b.price, b.bid_at, u.nom AS bidder_name, u.id AS bidder_id
            FROM `Bid` b
            JOIN `User` u ON u.id = b.user_id
            WHERE b.auction_id = ?
            ORDER BY b.bid_at ASC
        ");
        $stmt->execute([$auctionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function placeBid(int $auctionId, int $userId, float $price): bool
    {
        $a = new AuctionService();

        // Enchère active ?
        if (!$a->isActive($auctionId)) {
            return false;
        }

        // Seuil = max(min_price, meilleure offre courante)
        $threshold = $a->getCurrentThreshold($auctionId);
        if ($price <= $threshold) {
            return false;
        }

        $stmt = DB::pdo()->prepare("
            INSERT INTO `Bid` (auction_id, user_id, price, bid_at)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$auctionId, $userId, $price]);
    }

    public function delete(int $bidId, int $requestUserId): bool
    {
        // Suppression uniquement par l'auteur de l'offre
        $stmt = DB::pdo()->prepare("DELETE FROM `Bid` WHERE id = ? AND user_id = ?");
        $stmt->execute([$bidId, $requestUserId]);
        return $stmt->rowCount() > 0;
    }
}
