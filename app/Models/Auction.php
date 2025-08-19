<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use DateTime;

final class Auction
{
    public static function findById(int $id): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT a.*, s.name AS stamp_name, u.first_name, u.last_name
            FROM `Auction` a
            LEFT JOIN `Stamp` s ON s.id = a.stamp_id
            LEFT JOIN `User` u ON u.id = a.seller_id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findActiveAuctions(): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT a.*, s.name AS stamp_name, u.first_name, u.last_name,
                   si.url AS main_image
            FROM `Auction` a
            LEFT JOIN `Stamp` s ON s.id = a.stamp_id
            LEFT JOIN `User` u ON u.id = a.seller_id
            LEFT JOIN `StampImage` si ON si.stamp_id = a.stamp_id AND si.is_main = 1
            WHERE a.auction_end > NOW()
            ORDER BY a.auction_end ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByStampId(int $stampId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT a.*, u.first_name, u.last_name
            FROM `Auction` a
            LEFT JOIN `User` u ON u.id = a.seller_id
            WHERE a.stamp_id = ?
            ORDER BY a.auction_start DESC
        ");
        $stmt->execute([$stampId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findActiveByStampId(int $stampId): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT a.*, u.first_name, u.last_name
            FROM `Auction` a
            LEFT JOIN `User` u ON u.id = a.seller_id
            WHERE a.stamp_id = ? AND a.auction_end > NOW()
            ORDER BY a.auction_start DESC
            LIMIT 1
        ");
        $stmt->execute([$stampId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int|false
    {
        // Validate dates
        $start = new DateTime($data['auction_start']);
        $end = new DateTime($data['auction_end']);

        if ($end <= $start) {
            return false; // End date must be after start date
        }

        $stmt = DB::pdo()->prepare("
            INSERT INTO `Auction` (stamp_id, seller_id, auction_start, auction_end, min_price, favorite)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $data['stamp_id'],
            $data['seller_id'],
            $data['auction_start'],
            $data['auction_end'],
            $data['min_price'],
            ($data['favorite'] ?? false) ? 1 : 0,
        ]);

        return $success ? (int)DB::pdo()->lastInsertId() : false;
    }

    public static function update(int $id, array $data): bool
    {
        // Validate dates if provided
        if (isset($data['auction_start']) && isset($data['auction_end'])) {
            $start = new DateTime($data['auction_start']);
            $end = new DateTime($data['auction_end']);

            if ($end <= $start) {
                return false;
            }
        }

        $stmt = DB::pdo()->prepare("
            UPDATE `Auction`
            SET stamp_id = ?, seller_id = ?, auction_start = ?, auction_end = ?, min_price = ?, favorite = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['stamp_id'],
            $data['seller_id'],
            $data['auction_start'],
            $data['auction_end'],
            $data['min_price'],
            ($data['favorite'] ?? false) ? 1 : 0,
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        // Note: In a real app, you might want to check for existing bids first
        $stmt = DB::pdo()->prepare("DELETE FROM `Auction` WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function isStampInActiveAuction(int $stampId): bool
    {
        $stmt = DB::pdo()->prepare("
            SELECT 1 FROM `Auction` 
            WHERE stamp_id = ? AND auction_end > NOW() 
            LIMIT 1
        ");
        $stmt->execute([$stampId]);
        return (bool)$stmt->fetchColumn();
    }
}
