<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Config;
use App\Services\StampService;
use App\Core\DB;
use PDO;

class FavoriteController
{
    private $stampService;
    private $lordPassword = 'LordStampee1234!'; // Special password for Lord access

    public function __construct()
    {
        $this->stampService = new StampService();
    }

    /**
     * Show login form for Lord access
     */
    public function showLogin()
    {
        // Check if already authenticated
        if ($this->isLordAuthenticated()) {
            header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
            exit;
        }

        View::render('pages/favorites/login', [
            'title' => 'Accès Lord - Coups de Cœur',
            'error' => $_SESSION['lord_error'] ?? null
        ]);

        // Clear error message
        unset($_SESSION['lord_error']);
    }

    /**
     * Handle login authentication
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Config::get('app.base_url') . '/lord/login');
            exit;
        }

        $password = $_POST['password'] ?? '';

        if ($password === $this->lordPassword) {
            $_SESSION['lord_authenticated'] = true;
            header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
            exit;
        } else {
            $_SESSION['lord_error'] = 'Mot de passe incorrect';
            header('Location: ' . Config::get('app.base_url') . '/lord/login');
            exit;
        }
    }

    /**
     * Show management interface for favorites
     */
    public function manage()
    {
        if (!$this->isLordAuthenticated()) {
            header('Location: ' . Config::get('app.base_url') . '/lord/login');
            exit;
        }

        try {
            // Get all auctions with details
            $allAuctions = $this->getAllAuctionsWithDetails();
            // Get current favorites
            $currentFavorites = $this->getAllFavoriteAuctions();
            $favoriteAuctionIds = array_column($currentFavorites, 'auction_id');

            View::render('pages/favorites/manage-favorites', [
                'title' => 'Gestion des Coups de Cœur',
                'auctions' => $allAuctions,
                'favoriteAuctionIds' => $favoriteAuctionIds,
                'success' => $_SESSION['success_message'] ?? null,
                'error' => $_SESSION['error_message'] ?? null
            ]);
            unset($_SESSION['success_message'], $_SESSION['error_message']);
        } catch (\Exception $e) {
            View::render('pages/favorites/manage-favorites', [
                'title' => 'Gestion des Coups de Cœur',
                'error' => 'Erreur lors du chargement: ' . $e->getMessage(),
                'auctions' => [],
                'favoriteAuctionIds' => []
            ]);
        }
    }

    /**
     * Toggle favorite status for an auction
     */
    public function toggleFavorite()
    {
        if (!$this->isLordAuthenticated()) {
            header('Location: ' . Config::get('app.base_url') . '/lord/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
            exit;
        }
        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $userId = 1; // Lord user id, or use session if needed
        if ($auctionId > 0) {
            try {
                if ($action === 'add') {
                    if ($this->addAuctionToFavorites($userId, $auctionId)) {
                        $_SESSION['success_message'] = 'Enchère ajoutée aux Coups de Cœur';
                    } else {
                        $_SESSION['error_message'] = 'Erreur lors de l\'ajout de l\'enchère';
                    }
                } elseif ($action === 'remove') {
                    if ($this->removeAuctionFromFavorites($userId, $auctionId)) {
                        $_SESSION['success_message'] = 'Enchère retirée des Coups de Cœur';
                    } else {
                        $_SESSION['error_message'] = 'Erreur lors de la suppression de l\'enchère';
                    }
                }
            } catch (\Exception $e) {
                $_SESSION['error_message'] = 'Erreur: ' . $e->getMessage();
            }
        }
        header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
        exit;
    }

    /**
     * Logout from Lord interface
     */
    public function logout()
    {
        unset($_SESSION['lord_authenticated']);
        header('Location: ' . Config::get('app.base_url') . '/');
        exit;
    }

    /**
     * API endpoint to get current favorites (for home page)
     */
    public function getFavoritesApi()
    {
        header('Content-Type: application/json');

        try {
            $favorites = $this->getAllFavoriteAuctionsWithDetails();
            echo json_encode([
                'success' => true,
                'favorites' => $favorites
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if Lord is authenticated
     */
    private function isLordAuthenticated(): bool
    {
        return isset($_SESSION['lord_authenticated']) && $_SESSION['lord_authenticated'] === true;
    }

    /**
     * Get all auctions with details for management interface
     */
    private function getAllAuctionsWithDetails(): array
    {
        $sql = "
            SELECT a.id as auction_id, a.stamp_id, a.min_price, a.auction_start, a.auction_end,
                   s.name as stamp_name, s.country_code, s.user_id,
                   c.name_fr AS country_name,
                   u.nom as seller_name,
                   si.url AS main_image,
                   (SELECT MAX(b.price) FROM `Bid` b WHERE b.auction_id = a.id) AS current_price
            FROM `Auction` a
            INNER JOIN `Stamp` s ON a.stamp_id = s.id
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = a.seller_id
            LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
            ORDER BY a.auction_end DESC
        ";
        $stmt = DB::pdo()->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Get all favorite auctions (just auction IDs)
     */
    private function getAllFavoriteAuctions(): array
    {
        $stmt = DB::pdo()->query("SELECT auction_id FROM `Favorite` ORDER BY favorite_at DESC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Add an auction to favorites
     */
    private function addAuctionToFavorites(int $userId, int $auctionId): bool
    {
        $check = DB::pdo()->prepare("SELECT 1 FROM `Favorite` WHERE user_id = ? AND auction_id = ? LIMIT 1");
        $check->execute([$userId, $auctionId]);
        if ($check->fetchColumn()) {
            return true;
        }
        $stmt = DB::pdo()->prepare("INSERT INTO `Favorite` (user_id, auction_id, favorite_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $auctionId]);
    }

    /**
     * Remove an auction from favorites
     */
    private function removeAuctionFromFavorites(int $userId, int $auctionId): bool
    {
        $stmt = DB::pdo()->prepare("DELETE FROM `Favorite` WHERE user_id = ? AND auction_id = ?");
        return $stmt->execute([$userId, $auctionId]);
    }

    /**
     * Get all favorite auctions with full details
     */
    private function getAllFavoriteAuctionsWithDetails(): array
    {
        $sql = "
            SELECT f.id as favorite_id, f.auction_id, f.favorite_at,
                   a.stamp_id, a.min_price, a.auction_start, a.auction_end,
                   s.name as stamp_name, s.country_code, s.user_id,
                   c.name_fr AS country_name,
                   u.nom as seller_name,
                   si.url AS main_image,
                   (SELECT MAX(b.price) FROM `Bid` b WHERE b.auction_id = a.id) AS current_price
            FROM `Favorite` f
            INNER JOIN `Auction` a ON f.auction_id = a.id
            INNER JOIN `Stamp` s ON a.stamp_id = s.id
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = a.seller_id
            LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
            ORDER BY f.favorite_at DESC
        ";
        $stmt = DB::pdo()->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
