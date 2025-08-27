<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use DateTime;

final class Bid
{
    /**
     * Récupère toutes les offres pour une enchère donnée
     */
    public static function findByAuction(int $auctionId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT b.*, u.nom
            FROM `Bid` b
            JOIN `User` u ON u.id = b.user_id
            WHERE b.auction_id = ?
            ORDER BY b.price DESC, b.bid_at ASC
        ");
        $stmt->execute([$auctionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère la meilleure offre (la plus élevée) pour une enchère
     */
    public static function findHighestByAuction(int $auctionId): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT b.*, u.nom
            FROM `Bid` b
            JOIN `User` u ON u.id = b.user_id
            WHERE b.auction_id = ?
            ORDER BY b.price DESC, b.bid_at ASC
            LIMIT 1
        ");
        $stmt->execute([$auctionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère les offres d'un utilisateur pour une enchère spécifique
     */
    public static function findByUserAndAuction(int $userId, int $auctionId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT * FROM `Bid`
            WHERE user_id = ? AND auction_id = ?
            ORDER BY bid_at DESC
        ");
        $stmt->execute([$userId, $auctionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un utilisateur a déjà misé sur une enchère
     */
    public static function userHasBid(int $userId, int $auctionId): bool
    {
        $stmt = DB::pdo()->prepare("
            SELECT 1 FROM `Bid`
            WHERE user_id = ? AND auction_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId, $auctionId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Place une nouvelle offre
     */
    public static function create(array $data): int|false
    {
        // Validation des données requises
        if (!isset($data['auction_id'], $data['user_id'], $data['price'])) {
            return false;
        }

        $auctionId = (int)$data['auction_id'];
        $userId = (int)$data['user_id'];
        $price = (float)$data['price'];

        // Vérifications de validité
        if ($auctionId <= 0 || $userId <= 0 || $price <= 0) {
            return false;
        }

        $stmt = DB::pdo()->prepare("
            INSERT INTO `Bid` (auction_id, user_id, price, bid_at)
            VALUES (?, ?, ?, NOW())
        ");

        $success = $stmt->execute([$auctionId, $userId, $price]);
        return $success ? (int)DB::pdo()->lastInsertId() : false;
    }

    /**
     * Retire une offre (suppression physique)
     */
    public static function withdraw(int $bidId, int $userId): bool
    {
        $stmt = DB::pdo()->prepare("
            DELETE FROM `Bid` WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$bidId, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Récupère le nombre total d'offres pour une enchère
     */
    public static function countByAuction(int $auctionId): int
    {
        $stmt = DB::pdo()->prepare("
            SELECT COUNT(*) FROM `Bid`
            WHERE auction_id = ?
        ");
        $stmt->execute([$auctionId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère l'historique des offres d'un utilisateur
     */
    public static function findByUser(int $userId, int $limit = 50): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT b.*, a.auction_start, a.auction_end, s.name AS stamp_name
            FROM `Bid` b
            JOIN `Auction` a ON a.id = b.auction_id
            JOIN `Stamp` s ON s.id = a.stamp_id
            WHERE b.user_id = ?
            ORDER BY b.bid_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les offres récentes pour la section actualités
     */
    public static function findRecentBids(int $limit = 5): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT b.*, u.nom AS bidder_name, a.id AS auction_id, s.name AS stamp_name,
                   si.url AS stamp_image, a.auction_start, a.auction_end
            FROM `Bid` b
            JOIN `User` u ON u.id = b.user_id
            JOIN `Auction` a ON a.id = b.auction_id
            JOIN `Stamp` s ON s.id = a.stamp_id
            LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
            WHERE a.auction_end > NOW()  -- Only show bids from active or upcoming auctions
            ORDER BY b.bid_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
