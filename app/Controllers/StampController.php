<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\CsrfToken;
use App\Core\View;
use App\Core\DB;
use App\Services\AuctionService;
use App\Services\BidService;
use App\Services\StampService;
use App\Services\CountryService;

final class StampController
{
    use \App\Core\AuthHelper;

    private StampService $stampService;

    public function __construct()
    {
        $this->stampService = new StampService();
    }

    public function index(): void
    {
        // If user is authenticated, show only their stamps
        if (!empty($_SESSION['user'])) {
            $userId = $_SESSION['user']['id'];
            $stamps = $this->stampService->listByUserWithCountryAndMainImage($userId);
        } else {
            // For guests, redirect to public auctions or require login
            View::redirect('/auctions');
            return;
        }

        View::render('pages/stamps/index', ['stamps' => $stamps]);
    }

    public function show(array $data): void
    {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/stamps');
        }
        $stamp = $this->stampService->getByIdFull($id);
        if (!$stamp) {
            $this->redirectWithError('Timbre introuvable.', '/stamps');
        }
        View::render('pages/stamps/show', ['stamp' => $stamp]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $countries = (new CountryService())->listAll();
        View::render('pages/stamps/create', ['countries' => $countries]);
    }

    public function store(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/stamps/create');

        // Server-side validation for uploaded images
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif', 'bmp', 'tiff', 'tif', 'svg', 'ico', 'avif'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff', 'image/svg+xml', 'image/x-icon', 'image/avif'];
        $maxFiles = 5;
        $maxSize = 1 * 1024 * 1024; // 1MB

        if (!empty($_FILES['stamp_images']) && is_array($_FILES['stamp_images']['name'])) {
            $filesCount = count(array_filter($_FILES['stamp_images']['name']));
            if ($filesCount > $maxFiles) {
                $_SESSION['flash']['error'] = "Maximum de {$maxFiles} images autorisées.";
                View::redirect('/stamps/create');
            }

            // Use finfo to validate MIME types
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            for ($i = 0; $i < $filesCount; $i++) {
                if (!isset($_FILES['stamp_images']['tmp_name'][$i]) || !is_uploaded_file($_FILES['stamp_images']['tmp_name'][$i])) {
                    continue;
                }

                $size = $_FILES['stamp_images']['size'][$i];
                if ($size > $maxSize) {
                    $_SESSION['flash']['error'] = 'Une des images dépasse la taille maximale de 1MB.';
                    finfo_close($finfo);
                    View::redirect('/stamps/create');
                }

                $tmp = $_FILES['stamp_images']['tmp_name'][$i];
                $mime = finfo_file($finfo, $tmp);
                if (!in_array($mime, $allowedMime, true)) {
                    $_SESSION['flash']['error'] = 'Format d\'image non pris en charge (type MIME).';
                    finfo_close($finfo);
                    View::redirect('/stamps/create');
                }

                $name = $_FILES['stamp_images']['name'][$i];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    $_SESSION['flash']['error'] = 'Extension de fichier non autorisée pour une image.';
                    finfo_close($finfo);
                    View::redirect('/stamps/create');
                }
            }
            finfo_close($finfo);
        }

        // Validate auction fields if auction checkbox is checked
        if (!empty($data['create_auction'])) {
            $errors = [];

            $auctionStart = trim((string)($data['auction_start'] ?? ''));
            $auctionEnd = trim((string)($data['auction_end'] ?? ''));
            $minPrice = $data['min_price'] ?? null;

            if ($auctionStart === '' || $auctionEnd === '' || $minPrice === null) {
                $errors[] = 'Les champs d\'enchères sont obligatoires quand l\'option est cochée.';
            } else {
                // Validate datetime formats
                $startTs = strtotime($auctionStart);
                $endTs = strtotime($auctionEnd);

                if ($startTs === false || $endTs === false) {
                    $errors[] = 'Format de date invalide pour les enchères.';
                } else {
                    $now = time();
                    // Start must be in the future (at least 1 minute ahead)
                    if ($startTs < $now + 60) {
                        $errors[] = 'La date de début doit être dans le futur.';
                    }
                    if ($endTs <= $startTs) {
                        $errors[] = 'La date de fin doit être après la date de début.';
                    }
                }

                // Validate min price
                if (!is_numeric($minPrice) || (float)$minPrice <= 0) {
                    $errors[] = 'Le prix minimum doit être un nombre supérieur à 0.';
                }
            }

            if (!empty($errors)) {
                $_SESSION['flash']['error'] = implode(' ', $errors);
                View::redirect('/stamps/create');
            }
        }

        try {
            DB::pdo()->beginTransaction();
            $result = $this->stampService->createFromFormWithAuction($data);
            if ($result === false) {
                DB::pdo()->rollBack();
                $_SESSION['flash']['error'] = 'Erreur lors de la création du timbre.';
                View::redirect('/stamps/create');
            }
            DB::pdo()->commit();

            $_SESSION['flash']['success'] = 'Timbre créé avec succès et mis aux enchères.';

            // Store both IDs in session for potential future use
            $_SESSION['created_stamp'] = [
                'stamp_id' => $result['stamp_id'],
                'auction_id' => $result['auction_id']
            ];

            // Always redirect to auction since auction is required
            if (isset($result['auction_id']) && $result['auction_id']) {
                View::redirect("/auctions/show?id={$result['auction_id']}");
            } else {
                // Fallback to stamps list if auction creation failed
                View::redirect('/stamps');
            }
        } catch (\Throwable $e) {
            if (DB::pdo()->inTransaction()) {
                DB::pdo()->rollBack();
            }
            error_log('Error creating stamp: ' . $e->getMessage());
            error_log('Error file: ' . $e->getFile() . ':' . $e->getLine());
            error_log('POST data: ' . print_r($data, true));

            // Show detailed error in development
            $errorMsg = 'Une erreur serveur est survenue lors de la création du timbre.';
            if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                $errorMsg .= ' [Debug: ' . $e->getMessage() . ']';
            }

            $_SESSION['flash']['error'] = $errorMsg;
            View::redirect('/stamps/create');
        }
    }

    public function edit(array $data): void
    {
        $this->requireAuth();
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/stamps');
        }

        $userId = $_SESSION['user']['id'];
        $stamp = $this->stampService->getByIdAndUser($id, $userId);
        if (!$stamp) {
            $_SESSION['flash']['error'] = 'Timbre introuvable ou vous n\'êtes pas autorisé à le modifier.';
            View::redirect('/stamps');
        }

        $countries = (new CountryService())->listAll();
        View::render('pages/stamps/edit', ['stamp' => $stamp, 'countries' => $countries]);
    }

    public function update(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/stamps');

        $ok = $this->stampService->updateFromForm($data);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Timbre mis à jour.' : 'Mise à jour refusée ou timbre non trouvé.';
        View::redirect('/stamps');
    }

    public function delete(array $data): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($data['_token'] ?? null, '/stamps');

        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/stamps');
        }

        $ok = $this->stampService->delete($id);
        $_SESSION['flash'][$ok ? 'success' : 'error'] = $ok ? 'Timbre supprimé.' : 'Suppression refusée : timbre non trouvé, non autorisé, ou a des enchères avec des offres actives.';
        View::redirect('/stamps');
    }

    public function publicShow(array $data): void
    {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            View::redirect('/auctions');
        }

        $stSrv = new StampService();
        $auSrv = new AuctionService();

        $stamp = $stSrv->getByIdFull($id);
        if (!$stamp) {
            $_SESSION['flash']['error'] = 'Timbre introuvable.';
            View::redirect('/auctions');
        }

        // Optionnel: enchère active liée au timbre
        $auction = $auSrv->getActiveByStamp($id);

        // Offres (si besoin d’afficher historique)
        $bids = [];
        if ($auction) {
            $bids = (new BidService())->getByAuction((int)$auction['id']);
        }

        View::render('pages/stamps/index', [
            'stamp'   => $stamp,
            'auction' => $auction,
            'bids'    => $bids
        ]);
    }

    public function setMainImage(array $data): void
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }

        if (!CsrfToken::check($data['_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF invalide']);
            return;
        }

        $imageId = (int)($data['image_id'] ?? 0);
        if ($imageId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID image invalide']);
            return;
        }

        $success = $this->stampService->setMainImage($imageId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Image principale mise à jour' : 'Erreur lors de la mise à jour'
        ]);
    }

    public function deleteImage(array $data): void
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }

        if (!CsrfToken::check($data['_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF invalide']);
            return;
        }

        $imageId = (int)($data['image_id'] ?? 0);
        if ($imageId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID image invalide']);
            return;
        }

        $success = $this->stampService->deleteImage($imageId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Image supprimée' : 'Erreur lors de la suppression'
        ]);
    }
}
