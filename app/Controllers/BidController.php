<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\BidService;
use App\Services\AuctionService;

final class BidController
{
    use \App\Core\AuthHelper;

    /**
     * Place une nouvelle offre sur une enchère
     */
    public function store(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/auctions');

        $auctionId = (int)($data['auction_id'] ?? 0);
        $price = (float)($data['price'] ?? 0);

        if ($auctionId <= 0) {
            $this->redirectWithError('Enchère invalide.', '/auctions');
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $bidService = new BidService();

        // Placer l'offre avec validation complète
        $result = $bidService->placeBid($auctionId, $userId, $price);

        $redirectUrl = "/auctions/show?id=$auctionId";

        if ($result['success']) {
            $this->redirectWithSuccess($result['message'], $redirectUrl);
        } else {
            $errorMessage = implode(' ', $result['errors']);
            $this->redirectWithError($errorMessage, $redirectUrl);
        }
    }

    /**
     * Retire une offre (suppression logique)
     */
    public function delete(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/auctions');

        $bidId = (int)($data['id'] ?? 0);
        $auctionId = (int)($data['auction_id'] ?? 0);

        if ($bidId <= 0 || $auctionId <= 0) {
            View::redirect('/auctions');
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $bidService = new BidService();

        $result = $bidService->withdrawBid($bidId, $userId);
        $redirectUrl = "/auctions/show?id=$auctionId";

        if ($result['success']) {
            $this->redirectWithSuccess($result['message'], $redirectUrl);
        } else {
            $errorMessage = implode(' ', $result['errors']);
            $this->redirectWithError($errorMessage, $redirectUrl);
        }
    }

    /**
     * Affiche l'historique des offres d'un utilisateur
     */
    public function history(): void
    {
        $this->requireAuth();

        $userId = (int)$_SESSION['user']['id'];
        $bidService = new BidService();

        $bids = $bidService->getUserBidHistory($userId);
        $winningAuctions = $bidService->getUserWinningAuctions($userId);

        View::render('pages/bids/history', [
            'user_bids' => $bids,
            'winning_auctions' => $winningAuctions,
            'page_title' => 'Mon historique d\'enchères'
        ]);
    }

    /**
     * API endpoint pour vérifier si un utilisateur peut miser
     */
    public function canBid(): void
    {
        $this->requireAuth();

        $auctionId = (int)($_GET['auction_id'] ?? 0);

        if ($auctionId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID d\'enchère invalide']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $bidService = new BidService();

        $result = $bidService->canUserBid($auctionId, $userId);
        $minimumBid = $bidService->getMinimumNextBid($auctionId);

        header('Content-Type: application/json');
        echo json_encode([
            'can_bid' => $result['can_bid'],
            'errors' => $result['errors'],
            'minimum_bid' => $minimumBid
        ]);
    }

    /**
     * API endpoint pour récupérer les statistiques d'une enchère
     */
    public function auctionStats(): void
    {
        $auctionId = (int)($_GET['auction_id'] ?? 0);

        if ($auctionId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID d\'enchère invalide']);
            return;
        }

        $bidService = new BidService();

        $stats = $bidService->getAuctionStats($auctionId);
        $minimumBid = $bidService->getMinimumNextBid($auctionId);
        $bids = $bidService->getByAuction($auctionId);

        // Calculer le prix actuel
        $currentPrice = $stats['highest_bid'] > 0 ? $stats['highest_bid'] : 0;

        header('Content-Type: application/json');
        echo json_encode([
            'stats' => $stats,
            'minimum_bid' => $minimumBid,
            'current_price' => $currentPrice,
            'bids' => array_slice($bids, 0, 5), // Limiter à 5 enchères récentes
            'total_bids' => $stats['total_bids']
        ]);
    }

    /**
     * API endpoint pour placer une enchère via AJAX
     */
    public function ajaxStore(): void
    {
        $this->requireAuth();

        // Vérifier le token CSRF
        $token = $_POST['_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Token CSRF invalide']]);
            return;
        }

        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        if ($auctionId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Enchère invalide']]);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $bidService = new BidService();

        // Placer l'offre
        $result = $bidService->placeBid($auctionId, $userId, $price);

        header('Content-Type: application/json');

        if ($result['success']) {
            // Récupérer les données mises à jour
            $stats = $bidService->getAuctionStats($auctionId);
            $minimumBid = $bidService->getMinimumNextBid($auctionId);
            $bids = $bidService->getByAuction($auctionId);

            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'current_price' => $stats['highest_bid'],
                    'minimum_bid' => $minimumBid,
                    'total_bids' => $stats['total_bids'],
                    'stats' => $stats,
                    'bids' => array_slice($bids, 0, 5)
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'errors' => $result['errors']
            ]);
        }
    }

    private function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validation rapide d'un montant d'offre via AJAX
     */
    public function validateBidAmount(): void
    {
        $this->requireAuth();

        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        if ($auctionId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID d\'enchère invalide']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $bidService = new BidService();

        // Utiliser la validation du modèle Bid
        $errors = \App\Models\Bid::validateBid($auctionId, $userId, $price);

        header('Content-Type: application/json');
        echo json_encode([
            'valid' => empty($errors),
            'errors' => $errors,
            'minimum_bid' => $bidService->getMinimumNextBid($auctionId)
        ]);
    }
}
