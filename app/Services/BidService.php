<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use App\Models\Bid;
use App\Models\Auction;
use PDO;
use DateTime;

final class BidService
{
    /**
     * Récupère toutes les offres pour une enchère avec informations détaillées
     */
    public function getByAuction(int $auctionId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT b.id, b.price, b.bid_at, 
                   u.nom AS bidder_name, u.id AS bidder_id
            FROM `Bid` b
            JOIN `User` u ON u.id = b.user_id
            WHERE b.auction_id = ?
            ORDER BY b.price DESC, b.bid_at ASC
        ");
        $stmt->execute([$auctionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupère les statistiques d'offres pour une enchère
     */
    public function getAuctionStats(int $auctionId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT 
                COUNT(*) as total_bids,
                MAX(price) as highest_bid,
                MIN(price) as lowest_bid,
                AVG(price) as average_bid,
                COUNT(DISTINCT user_id) as unique_bidders
            FROM `Bid`
            WHERE auction_id = ?
        ");
        $stmt->execute([$auctionId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Convertir les valeurs nulles en 0
        return [
            'total_bids' => (int)($stats['total_bids'] ?? 0),
            'highest_bid' => (float)($stats['highest_bid'] ?? 0),
            'lowest_bid' => (float)($stats['lowest_bid'] ?? 0),
            'average_bid' => (float)($stats['average_bid'] ?? 0),
            'unique_bidders' => (int)($stats['unique_bidders'] ?? 0)
        ];
    }

    /**
     * Place une offre avec validation complète
     */
    public function placeBid(int $auctionId, int $userId, float $price): array
    {
        // Validation préliminaire
        $validationErrors = $this->validateBid($auctionId, $userId, $price);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'errors' => $validationErrors,
                'bid_id' => null
            ];
        }

        // Vérifications supplémentaires de sécurité
        $auction = $this->getAuctionById($auctionId);
        if (!$auction) {
            return [
                'success' => false,
                'errors' => ['Enchère introuvable.'],
                'bid_id' => null
            ];
        }

        // Vérifier l'activité de l'enchère
        if (!$this->isAuctionActive($auctionId)) {
            return [
                'success' => false,
                'errors' => ['L\'enchère n\'est pas active.'],
                'bid_id' => null
            ];
        }

        // Vérifier le seuil de prix
        $threshold = $this->getCurrentThreshold($auctionId);
        if ($price <= $threshold) {
            $required = $threshold + 0.01;
            return [
                'success' => false,
                'errors' => ["Votre offre doit être supérieure à " . number_format($required, 2) . " $ CAD."],
                'bid_id' => null
            ];
        }

        // Tenter de créer l'offre
        try {
            $stmt = DB::pdo()->prepare("
                INSERT INTO `Bid` (auction_id, user_id, price, bid_at)
                VALUES (?, ?, ?, NOW())
            ");

            $success = $stmt->execute([$auctionId, $userId, $price]);

            if ($success) {
                $bidId = (int)DB::pdo()->lastInsertId();
                return [
                    'success' => true,
                    'errors' => [],
                    'bid_id' => $bidId,
                    'message' => 'Offre placée avec succès!'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Erreur lors de la création de l\'offre.'],
                    'bid_id' => null
                ];
            }
        } catch (\Exception $e) {
            error_log('Erreur lors du placement d\'offre: ' . $e->getMessage());

            return [
                'success' => false,
                'errors' => ['Erreur technique lors du placement de l\'offre.'],
                'bid_id' => null
            ];
        }
    }

    /**
     * Retire une offre (suppression physique pour votre structure actuelle)
     */
    public function withdrawBid(int $bidId, int $userId): array
    {
        try {
            // Vérifier que l'offre appartient à l'utilisateur
            $stmt = DB::pdo()->prepare("
                SELECT auction_id FROM `Bid`
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$bidId, $userId]);
            $auctionId = $stmt->fetchColumn();

            if (!$auctionId) {
                return [
                    'success' => false,
                    'errors' => ['Offre non trouvée ou n\'appartient pas à l\'utilisateur.']
                ];
            }

            // Vérifier que l'enchère est toujours active
            if (!$this->isAuctionActive((int)$auctionId)) {
                return [
                    'success' => false,
                    'errors' => ['Impossible de retirer une offre sur une enchère terminée.']
                ];
            }

            // Supprimer l'offre
            $stmt = DB::pdo()->prepare("DELETE FROM `Bid` WHERE id = ? AND user_id = ?");
            $stmt->execute([$bidId, $userId]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Offre retirée avec succès.'
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Impossible de retirer cette offre.']
                ];
            }
        } catch (\Exception $e) {
            error_log('Erreur lors du retrait d\'offre: ' . $e->getMessage());

            return [
                'success' => false,
                'errors' => ['Erreur technique lors du retrait de l\'offre.']
            ];
        }
    }

    /**
     * Méthode de compatibilité avec l'ancien code
     */
    public function delete(int $bidId, int $requestUserId): bool
    {
        $result = $this->withdrawBid($bidId, $requestUserId);
        return $result['success'];
    }

    /**
     * Vérifie si un utilisateur peut placer une offre
     */
    public function canUserBid(int $auctionId, int $userId): array
    {
        $errors = $this->validateBid($auctionId, $userId, 1.0); // Montant temporaire pour validation

        // Enlever l'erreur de montant pour cette vérification
        $errors = array_filter($errors, function ($error) {
            return !str_contains($error, 'montant') && !str_contains($error, 'offre doit être');
        });

        return [
            'can_bid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Récupère le prochain montant minimum pour une offre
     */
    public function getMinimumNextBid(int $auctionId): float
    {
        $threshold = $this->getCurrentThreshold($auctionId);
        return $threshold + 0.01; // Incrément minimum de 1 centime
    }

    /**
     * Validation uniquement d'une offre (sans la placer)
     */
    public function validateBidOnly(int $auctionId, int $userId, float $price): array
    {
        return $this->validateBid($auctionId, $userId, $price);
    }

    /**
     * Récupère l'historique des offres pour un utilisateur
     */
    public function getUserBidHistory(int $userId, int $limit = 20): array
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Vérifie si un utilisateur est le meilleur enchérisseur
     */
    public function isUserWinning(int $auctionId, int $userId): bool
    {
        $stmt = DB::pdo()->prepare("
            SELECT user_id FROM `Bid`
            WHERE auction_id = ?
            ORDER BY price DESC, bid_at ASC
            LIMIT 1
        ");
        $stmt->execute([$auctionId]);
        $winningUserId = $stmt->fetchColumn();

        return $winningUserId && (int)$winningUserId === $userId;
    }

    /**
     * Récupère les enchères sur lesquelles un utilisateur est en tête
     */
    public function getUserWinningAuctions(int $userId): array
    {
        $stmt = DB::pdo()->prepare("
            SELECT DISTINCT a.*, s.name AS stamp_name, b.price AS winning_bid
            FROM `Auction` a
            JOIN `Stamp` s ON s.id = a.stamp_id
            JOIN `Bid` b ON b.auction_id = a.id
            WHERE b.user_id = ? 
              AND NOW() BETWEEN a.auction_start AND a.auction_end
              AND b.price = (
                  SELECT MAX(b2.price) 
                  FROM `Bid` b2 
                  WHERE b2.auction_id = a.id
              )
            ORDER BY a.auction_end ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Méthodes d'aide privées

    private function validateBid(int $auctionId, int $userId, float $price): array
    {
        $errors = [];

        // Vérifier que l'enchère existe
        $auction = $this->getAuctionById($auctionId);
        if (!$auction) {
            $errors[] = "Enchère introuvable.";
            return $errors;
        }

        // Vérifier les dates d'enchère
        $now = new DateTime();
        $auctionStart = new DateTime($auction['auction_start']);
        $auctionEnd = new DateTime($auction['auction_end']);

        if ($now < $auctionStart) {
            $errors[] = "L'enchère n'a pas encore commencé.";
        }

        if ($now > $auctionEnd) {
            $errors[] = "L'enchère est terminée.";
        }

        // Vérifier que l'utilisateur n'est pas le vendeur
        if ((int)$auction['seller_id'] === $userId) {
            $errors[] = "Vous ne pouvez pas miser sur votre propre enchère.";
        }

        // Vérifier le montant minimum
        if ($price <= 0) {
            $errors[] = "Le montant de l'offre doit être positif.";
        }

        $minPrice = (float)$auction['min_price'];
        if ($price < $minPrice) {
            $errors[] = "L'offre doit être d'au moins " . number_format($minPrice, 2) . " $ CAD.";
        }

        // Vérifier par rapport aux autres offres
        $currentHighest = $this->getCurrentThreshold($auctionId);
        if ($price <= $currentHighest) {
            $required = $currentHighest + 0.01;
            $errors[] = "Votre offre doit être supérieure à " . number_format($required, 2) . " $ CAD.";
        }

        return $errors;
    }

    private function getAuctionById(int $auctionId): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT * FROM `Auction` WHERE id = ?
        ");
        $stmt->execute([$auctionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function isAuctionActive(int $auctionId): bool
    {
        $auction = $this->getAuctionById($auctionId);
        if (!$auction) return false;

        $now = new DateTime();
        $start = new DateTime($auction['auction_start']);
        $end = new DateTime($auction['auction_end']);

        return $now >= $start && $now <= $end;
    }

    private function getCurrentThreshold(int $auctionId): float
    {
        $auction = $this->getAuctionById($auctionId);
        if (!$auction) return 0.0;

        $stmt = DB::pdo()->prepare("SELECT MAX(price) FROM `Bid` WHERE auction_id = ?");
        $stmt->execute([$auctionId]);
        $maxBid = (float)($stmt->fetchColumn() ?: 0);

        return max((float)$auction['min_price'], $maxBid);
    }
}
