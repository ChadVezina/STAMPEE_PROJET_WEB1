<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Stamp
{
    public static function findById(int $id): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT s.*, c.name_fr AS country_name, u.first_name, u.last_name
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = s.user_id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByIdAndUser(int $id, int $userId): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT s.*, c.name_fr AS country_name, u.first_name, u.last_name
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = s.user_id
            WHERE s.id = ? AND s.user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findAll(): array
    {
        $stmt = DB::pdo()->query("
            SELECT s.id, s.name, s.user_id, s.created_at, s.country_code, c.name_fr AS country_name,
                   s.width_mm, s.height_mm, s.current_state, s.nbr_stamps, s.dimensions, s.certified,
                   u.first_name, u.last_name
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = s.user_id
            ORDER BY s.name ASC
        ");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function findByUser(int $userId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT s.id, s.name, s.user_id, s.created_at, s.country_code, c.name_fr AS country_name,
                   s.width_mm, s.height_mm, s.current_state, s.nbr_stamps, s.dimensions, s.certified
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            WHERE s.user_id = ?
            ORDER BY s.name ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int|false
    {
        $stmt = DB::pdo()->prepare("
            INSERT INTO `Stamp` (name, user_id, created_at, country_code, width_mm, height_mm, current_state, nbr_stamps, dimensions, certified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $data['name'],
            $data['user_id'], // Required user_id
            $data['created_at'] ?? null,
            $data['country_code'] ?? null,
            $data['width_mm'] ?? null,
            $data['height_mm'] ?? null,
            $data['current_state'] ?? null,
            $data['nbr_stamps'] ?? null,
            $data['dimensions'] ?? null,
            ($data['certified'] ?? false) ? 1 : 0,
        ]);

        return $success ? (int)DB::pdo()->lastInsertId() : false;
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = DB::pdo()->prepare("
            UPDATE `Stamp`
            SET name = ?, created_at = ?, country_code = ?, width_mm = ?, height_mm = ?,
                current_state = ?, nbr_stamps = ?, dimensions = ?, certified = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['name'],
            $data['created_at'] ?? null,
            $data['country_code'] ?? null,
            $data['width_mm'] ?? null,
            $data['height_mm'] ?? null,
            $data['current_state'] ?? null,
            $data['nbr_stamps'] ?? null,
            $data['dimensions'] ?? null,
            ($data['certified'] ?? false) ? 1 : 0,
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function updateByUser(int $id, int $userId, array $data): bool
    {
        $stmt = DB::pdo()->prepare("
            UPDATE `Stamp`
            SET name = ?, created_at = ?, country_code = ?, width_mm = ?, height_mm = ?,
                current_state = ?, nbr_stamps = ?, dimensions = ?, certified = ?
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([
            $data['name'],
            $data['created_at'] ?? null,
            $data['country_code'] ?? null,
            $data['width_mm'] ?? null,
            $data['height_mm'] ?? null,
            $data['current_state'] ?? null,
            $data['nbr_stamps'] ?? null,
            $data['dimensions'] ?? null,
            ($data['certified'] ?? false) ? 1 : 0,
            $id,
            $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        // Check if stamp has active auctions
        $auctionCheck = DB::pdo()->prepare("SELECT 1 FROM `Auction` WHERE stamp_id = ? LIMIT 1");
        $auctionCheck->execute([$id]);
        if ($auctionCheck->fetchColumn()) {
            return false; // Cannot delete stamp with active auctions
        }

        // Delete associated images first
        DB::pdo()->prepare("DELETE FROM `StampImage` WHERE stamp_id = ?")->execute([$id]);

        // Delete stamp
        $stmt = DB::pdo()->prepare("DELETE FROM `Stamp` WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function deleteByUser(int $id, int $userId): bool
    {
        // Check if stamp has active auctions
        $auctionCheck = DB::pdo()->prepare("SELECT 1 FROM `Auction` WHERE stamp_id = ? LIMIT 1");
        $auctionCheck->execute([$id]);
        if ($auctionCheck->fetchColumn()) {
            return false; // Cannot delete stamp with active auctions
        }

        // Delete associated images first (only for this user's stamp)
        DB::pdo()->prepare("
            DELETE si FROM `StampImage` si 
            INNER JOIN `Stamp` s ON s.id = si.stamp_id 
            WHERE si.stamp_id = ? AND s.user_id = ?
        ")->execute([$id, $userId]);

        // Delete stamp (only if owned by user)
        $stmt = DB::pdo()->prepare("DELETE FROM `Stamp` WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);

        return $stmt->rowCount() > 0;
    }

    public static function getValidStates(): array
    {
        return ['Parfaite', 'Excellente', 'Bonne', 'Moyenne', 'Endommagée'];
    }
}
