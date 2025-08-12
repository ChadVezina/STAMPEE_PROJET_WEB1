<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;
use DateTimeImmutable;

final class AuctionService
{
    /** @return array<int, array<string,mixed>> */
    public function getAllWithMeta(): array
    {
        $sql = "
            SELECT a.*,
                   s.name AS stamp_name,
                   u.nom  AS seller_name,
                   (SELECT MAX(b.price) FROM `Bid` b WHERE b.auction_id = a.id) AS current_price,
                   (
                     a.favorite = 1
                     OR EXISTS (SELECT 1 FROM `Favorite` f WHERE f.auction_id = a.id)
                   ) AS is_featured
            FROM `Auction` a
            JOIN `Stamp`  s ON s.id = a.stamp_id
            JOIN `User`   u ON u.id = a.seller_id
            ORDER BY a.auction_start DESC
        ";
        $stmt = DB::pdo()->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT a.*, s.name AS stamp_name, u.nom AS seller_name
            FROM `Auction` a
            JOIN `Stamp`  s ON s.id = a.stamp_id
            JOIN `User`   u ON u.id = a.seller_id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByIdWithMeta(int $id): ?array
    {
        $row = $this->getById($id);
        if (!$row) return null;

        $stmt = DB::pdo()->prepare("SELECT MAX(price) FROM `Bid` WHERE auction_id = ?");
        $stmt->execute([$id]);
        $row['current_price'] = (float)($stmt->fetchColumn() ?: 0);

        $stmt = DB::pdo()->prepare("
            SELECT (a.favorite = 1)
                   OR EXISTS (SELECT 1 FROM `Favorite` f WHERE f.auction_id = a.id)
            FROM `Auction` a WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $row['is_featured'] = (bool)$stmt->fetchColumn();

        return $row;
    }


    public function create(int $stampId, int $sellerId, string $start, string $end, float $minPrice, bool $favorite): ?int
    {
        $dtStart = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $start) ?: new DateTimeImmutable($start);
        $dtEnd   = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $end)   ?: new DateTimeImmutable($end);
        if ($minPrice <= 0 || $dtEnd <= $dtStart) {
            return null;
        }

        $stmt = DB::pdo()->prepare("
            INSERT INTO `Auction` (stamp_id, seller_id, auction_start, auction_end, min_price, favorite)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $ok = $stmt->execute([
            $stampId,
            $sellerId,
            $dtStart->format('Y-m-d H:i:s'),
            $dtEnd->format('Y-m-d H:i:s'),
            $minPrice,
            $favorite ? 1 : 0,
        ]);
        if (!$ok) return null;

        return (int)DB::pdo()->lastInsertId() ?: null;
    }

    public function update(
        int $id,
        int $sellerIdOwner,
        int $stampId,
        string $start,
        string $end,
        float $minPrice,
        bool $favorite
    ): bool {
        $auction = $this->getById($id);
        if (!$auction || (int)$auction['seller_id'] !== $sellerIdOwner) {
            return false;
        }

        $dtStart = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $start) ?: new DateTimeImmutable($start);
        $dtEnd   = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $end)   ?: new DateTimeImmutable($end);
        if ($minPrice <= 0 || $dtEnd <= $dtStart) {
            return false;
        }

        $stmt = DB::pdo()->prepare("
            UPDATE `Auction`
               SET stamp_id = ?, auction_start = ?, auction_end = ?, min_price = ?, favorite = ?
             WHERE id = ? AND seller_id = ?
        ");
        $stmt->execute([
            $stampId,
            $dtStart->format('Y-m-d H:i:s'),
            $dtEnd->format('Y-m-d H:i:s'),
            $minPrice,
            $favorite ? 1 : 0,
            $id,
            $sellerIdOwner,
        ]);
        return $stmt->rowCount() > 0 || $this->existsForSeller($id, $sellerIdOwner);
    }

    public function delete(int $id, int $sellerIdOwner): bool
    {
        $stmt = DB::pdo()->prepare("DELETE FROM `Auction` WHERE id = ? AND seller_id = ?");
        $stmt->execute([$id, $sellerIdOwner]);
        return $stmt->rowCount() > 0;
    }

    public function getCurrentThreshold(int $auctionId): float
    {
        $stmt = DB::pdo()->prepare("SELECT min_price FROM `Auction` WHERE id = ?");
        $stmt->execute([$auctionId]);
        $min = (float)($stmt->fetchColumn() ?: 0);

        $stmt = DB::pdo()->prepare("SELECT MAX(price) FROM `Bid` WHERE auction_id = ?");
        $stmt->execute([$auctionId]);
        $max = (float)($stmt->fetchColumn() ?: 0);

        return max($min, $max);
    }

    public function isActive(int $auctionId, ?DateTimeImmutable $now = null): bool
    {
        $now = $now ?: new DateTimeImmutable('now');
        $stmt = DB::pdo()->prepare("SELECT auction_start, auction_end FROM `Auction` WHERE id = ?");
        $stmt->execute([$auctionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        $start = new DateTimeImmutable($row['auction_start']);
        $end   = new DateTimeImmutable($row['auction_end']);
        return $now >= $start && $now <= $end;
    }

    private function existsForSeller(int $id, int $sellerId): bool
    {
        $stmt = DB::pdo()->prepare("SELECT 1 FROM `Auction` WHERE id = ? AND seller_id = ? LIMIT 1");
        $stmt->execute([$id, $sellerId]);
        return (bool)$stmt->fetchColumn();
    }
}
