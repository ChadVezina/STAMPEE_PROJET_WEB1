<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class StampImage
{
    public static function findByStampId(int $stampId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT id, stamp_id, url, is_main
            FROM `StampImage`
            WHERE stamp_id = ?
            ORDER BY is_main DESC, id ASC
        ");
        $stmt->execute([$stampId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findMainImageByStampId(int $stampId): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT id, stamp_id, url, is_main
            FROM `StampImage`
            WHERE stamp_id = ? AND is_main = 1
            LIMIT 1
        ");
        $stmt->execute([$stampId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(int $stampId, string $url, bool $isMain = false): int|false
    {
        // If this is set as main image, unset other main images for this stamp
        if ($isMain) {
            self::unsetMainImage($stampId);
        }

        $stmt = DB::pdo()->prepare("
            INSERT INTO `StampImage` (stamp_id, url, is_main)
            VALUES (?, ?, ?)
        ");

        $success = $stmt->execute([$stampId, $url, $isMain ? 1 : 0]);
        return $success ? (int)DB::pdo()->lastInsertId() : false;
    }

    public static function setAsMain(int $imageId, int $stampId): bool
    {
        // First unset all main images for this stamp
        self::unsetMainImage($stampId);

        // Then set this image as main
        $stmt = DB::pdo()->prepare("UPDATE `StampImage` SET is_main = 1 WHERE id = ? AND stamp_id = ?");
        $stmt->execute([$imageId, $stampId]);

        return $stmt->rowCount() > 0;
    }

    public static function unsetMainImage(int $stampId): bool
    {
        $stmt = DB::pdo()->prepare("UPDATE `StampImage` SET is_main = 0 WHERE stamp_id = ?");
        $stmt->execute([$stampId]);

        return true;
    }

    public static function delete(int $id): bool
    {
        $stmt = DB::pdo()->prepare("DELETE FROM `StampImage` WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function deleteByStampId(int $stampId): bool
    {
        $stmt = DB::pdo()->prepare("DELETE FROM `StampImage` WHERE stamp_id = ?");
        $stmt->execute([$stampId]);

        return true;
    }
}
