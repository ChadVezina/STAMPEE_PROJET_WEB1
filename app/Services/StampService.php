<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;

final class StampService
{
    public function getAllBasic(): array
    {
        $stmt = DB::pdo()->query("SELECT id, name FROM `Stamp` ORDER BY name ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function listWithCountryAndMainImage(): array
    {
        $sql = "
            SELECT s.id, s.name, s.country_code, c.name_fr AS country_name,
                   si.url AS main_image
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
            ORDER BY s.name ASC
        ";
        $stmt = DB::pdo()->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getByIdFull(int $id): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT s.*, c.name_fr AS country_name
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $stamp = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$stamp) return null;

        $img = DB::pdo()->prepare("SELECT id, url, is_main FROM `StampImage` WHERE stamp_id = ? ORDER BY is_main DESC, id ASC");
        $img->execute([$id]);
        $stamp['images'] = $img->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $stamp;
    }

    public function createFromForm(array $data): bool
    {
        $name = trim($data['name'] ?? '');
        if ($name === '') return false;

        $country = $data['country_code'] ?? null;
        $created = $data['created_at'] ?? null; // 'Y-m-d' attendu
        $width   = isset($data['width_mm'])  ? (float)$data['width_mm']  : null;
        $height  = isset($data['height_mm']) ? (float)$data['height_mm'] : null;
        $state   = $data['current_state'] ?? null; // ENUM
        $nbr     = isset($data['nbr_stamps']) ? (int)$data['nbr_stamps'] : null;
        $dims    = $data['dimensions'] ?? null;
        $cert    = isset($data['certified']) && $data['certified'] === '1';

        $stmt = DB::pdo()->prepare("
            INSERT INTO `Stamp` (name, created_at, country_code, width_mm, height_mm, current_state, nbr_stamps, dimensions, certified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $name,
            $created ?: null,
            $country ?: null,
            $width,
            $height,
            $state ?: null,
            $nbr,
            $dims ?: null,
            $cert ? 1 : 0,
        ]);
    }

    public function updateFromForm(array $data): bool
    {
        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        if ($id <= 0 || $name === '') return false;

        $country = $data['country_code'] ?? null;
        $created = $data['created_at'] ?? null;
        $width   = isset($data['width_mm'])  ? (float)$data['width_mm']  : null;
        $height  = isset($data['height_mm']) ? (float)$data['height_mm'] : null;
        $state   = $data['current_state'] ?? null;
        $nbr     = isset($data['nbr_stamps']) ? (int)$data['nbr_stamps'] : null;
        $dims    = $data['dimensions'] ?? null;
        $cert    = isset($data['certified']) && $data['certified'] === '1';

        $stmt = DB::pdo()->prepare("
            UPDATE `Stamp`
               SET name = ?, created_at = ?, country_code = ?, width_mm = ?, height_mm = ?,
                   current_state = ?, nbr_stamps = ?, dimensions = ?, certified = ?
             WHERE id = ?
        ");
        $stmt->execute([
            $name,
            $created ?: null,
            $country ?: null,
            $width,
            $height,
            $state ?: null,
            $nbr,
            $dims ?: null,
            $cert ? 1 : 0,
            $id
        ]);

        if ($stmt->rowCount() > 0) return true;
        $chk = DB::pdo()->prepare("SELECT 1 FROM `Stamp` WHERE id = ? LIMIT 1");
        $chk->execute([$id]);
        return (bool)$chk->fetchColumn();
    }

    public function delete(int $id): bool
    {
        // refuse si lié à des enchères
        $chk = DB::pdo()->prepare("SELECT 1 FROM `Auction` WHERE stamp_id = ? LIMIT 1");
        $chk->execute([$id]);
        if ($chk->fetchColumn()) return false;

        DB::pdo()->prepare("DELETE FROM `StampImage` WHERE stamp_id = ?")->execute([$id]);

        $stmt = DB::pdo()->prepare("DELETE FROM `Stamp` WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
