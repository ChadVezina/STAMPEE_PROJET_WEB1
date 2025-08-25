<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Favorite
{
    public static function findByUserId(int $userId): array
    {
        $stmt = DB::pdo()->prepare("SELECT * FROM `favorite` WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteByUserId(int $userId): bool
    {
        $stmt = DB::pdo()->prepare("DELETE FROM `favorite` WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public static function isFavorite(int $userId, int $auctionId): bool
    {
        $stmt = DB::pdo()->prepare("SELECT COUNT(*) FROM `favorite` WHERE user_id = ? AND auction_id = ?");
        $stmt->execute([$userId, $auctionId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function add(int $userId, int $auctionId): bool
    {
        $stmt = DB::pdo()->prepare("INSERT IGNORE INTO `favorite` (user_id, auction_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $auctionId]);
    }

    public static function remove(int $userId, int $auctionId): bool
    {
        $stmt = DB::pdo()->prepare("DELETE FROM `favorite` WHERE user_id = ? AND auction_id = ?");
        return $stmt->execute([$userId, $auctionId]);
    }
}
