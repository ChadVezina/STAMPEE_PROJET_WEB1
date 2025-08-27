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
            SELECT s.id, s.name, s.user_id, s.country_code, c.name_fr AS country_name,
                   si.url AS main_image, u.nom
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = s.user_id
            LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
            ORDER BY s.name ASC
        ";
        $stmt = DB::pdo()->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function listByUserWithCountryAndMainImage(int $userId): array
    {
        $sql = "
            SELECT s.id, s.name, s.user_id, s.country_code, c.name_fr AS country_name,
                   si.url AS main_image
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
            WHERE s.user_id = ?
            ORDER BY s.name ASC
        ";
        $stmt = DB::pdo()->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getByIdFull(int $id): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT s.*, c.name_fr AS country_name, u.nom
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = s.user_id
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

    public function getByIdAndUser(int $id, int $userId): ?array
    {
        $stmt = DB::pdo()->prepare("
            SELECT s.*, c.name_fr AS country_name, u.nom
            FROM `Stamp` s
            LEFT JOIN `Country` c ON c.iso2 = s.country_code
            LEFT JOIN `User` u ON u.id = s.user_id
            WHERE s.id = ? AND s.user_id = ?
        ");
        $stmt->execute([$id, $userId]);
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

        // Get user ID from session
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return false;

        $country = $data['country_code'] ?? null;
        $created = $data['created_at'] ?? null; // 'Y-m-d' attendu
        $width   = isset($data['width_mm'])  ? (float)$data['width_mm']  : null;
        $height  = isset($data['height_mm']) ? (float)$data['height_mm'] : null;
        $state   = $data['current_state'] ?? null; // ENUM
        $nbr     = isset($data['nbr_stamps']) ? (int)$data['nbr_stamps'] : null;
        $dims    = $data['dimensions'] ?? null;
        $cert    = isset($data['certified']) && $data['certified'] === '1';

        // Validate state if provided
        if ($state && !in_array($state, ['Parfaite', 'Excellente', 'Bonne', 'Moyenne', 'Endommagée'])) {
            return false;
        }

        $stmt = DB::pdo()->prepare("
            INSERT INTO `Stamp` (name, user_id, created_at, country_code, width_mm, height_mm, current_state, nbr_stamps, dimensions, certified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $name,
            $userId,
            $created ?: null,
            $country ?: null,
            $width,
            $height,
            $state ?: null,
            $nbr,
            $dims ?: null,
            $cert ? 1 : 0,
        ]);

        if ($success) {
            $stampId = (int)DB::pdo()->lastInsertId();

            // Handle image uploads
            if (!empty($_FILES['stamp_images']['name'][0])) {
                $this->handleImageUploads($stampId, $_FILES['stamp_images']);
            }
        }

        return $success;
    }

    public function createFromFormWithAuction(array $data): array|false
    {
        $name = trim($data['name'] ?? '');
        if ($name === '') return false;

        // Get user ID from session
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return false;

        $country = $data['country_code'] ?? null;
        $created = $data['created_at'] ?? null;
        $width   = isset($data['width_mm'])  ? (float)$data['width_mm']  : null;
        $height  = isset($data['height_mm']) ? (float)$data['height_mm'] : null;
        $state   = $data['current_state'] ?? null;
        $nbr     = isset($data['nbr_stamps']) ? (int)$data['nbr_stamps'] : null;
        $dims    = $data['dimensions'] ?? null;
        $cert    = isset($data['certified']) && $data['certified'] === '1';

        // Validate state if provided
        if ($state && !in_array($state, ['Parfaite', 'Excellente', 'Bonne', 'Moyenne', 'Endommagée'])) {
            return false;
        }

        $stmt = DB::pdo()->prepare("
            INSERT INTO `Stamp` (name, user_id, created_at, country_code, width_mm, height_mm, current_state, nbr_stamps, dimensions, certified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $name,
            $userId,
            $created ?: null,
            $country ?: null,
            $width,
            $height,
            $state ?: null,
            $nbr,
            $dims ?: null,
            $cert ? 1 : 0,
        ]);

        if (!$success) return false;

        $stampId = (int)DB::pdo()->lastInsertId();

        // Handle image uploads
        if (!empty($_FILES['stamp_images']['name'][0])) {
            $this->handleImageUploads($stampId, $_FILES['stamp_images']);
        }

        $auctionId = null;

        // Create auction if requested
        if (!empty($data['create_auction']) && !empty($data['auction_start']) && !empty($data['auction_end']) && !empty($data['min_price'])) {
            $auctionData = [
                'stamp_id' => $stampId,
                'seller_id' => $userId, // Use the same user ID
                'auction_start' => $data['auction_start'],
                'auction_end' => $data['auction_end'],
                'min_price' => (float)$data['min_price'],
                'favorite' => !empty($data['favorite'])
            ];

            // Use Auction model to create the auction
            $auctionId = \App\Models\Auction::create($auctionData);
            if (!$auctionId) {
                // Log error but don't fail stamp creation
                error_log("Failed to create auction for stamp {$stampId}");
            }
        }

        return [
            'stamp_id' => $stampId,
            'auction_id' => $auctionId
        ];
    }

    public function updateFromForm(array $data): bool
    {
        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        if ($id <= 0 || $name === '') return false;

        // Check if user is authenticated
        if (!isset($_SESSION['user']['id'])) {
            return false;
        }

        $userId = $_SESSION['user']['id'];

        $country = $data['country_code'] ?? null;
        $created = $data['created_at'] ?? null;
        $width   = isset($data['width_mm'])  ? (float)$data['width_mm']  : null;
        $height  = isset($data['height_mm']) ? (float)$data['height_mm'] : null;
        $state   = $data['current_state'] ?? null;
        $nbr     = isset($data['nbr_stamps']) ? (int)$data['nbr_stamps'] : null;
        $dims    = $data['dimensions'] ?? null;
        $cert    = isset($data['certified']) && $data['certified'] === '1';

        // Validate state if provided
        if ($state && !in_array($state, ['Parfaite', 'Excellente', 'Bonne', 'Moyenne', 'Endommagée'])) {
            return false;
        }

        $stmt = DB::pdo()->prepare("
            UPDATE `Stamp`
               SET name = ?, created_at = ?, country_code = ?, width_mm = ?, height_mm = ?,
                   current_state = ?, nbr_stamps = ?, dimensions = ?, certified = ?
             WHERE id = ? AND user_id = ?
        ");
        $success = $stmt->execute([
            $name,
            $created ?: null,
            $country ?: null,
            $width,
            $height,
            $state ?: null,
            $nbr,
            $dims ?: null,
            $cert ? 1 : 0,
            $id,
            $userId
        ]);

        // Handle new image uploads
        if ($success && !empty($_FILES['stamp_images']['name'][0])) {
            $this->handleImageUploads($id, $_FILES['stamp_images']);
        }

        // If no rows were affected, check if the stamp exists and belongs to user
        if ($stmt->rowCount() === 0) {
            $chk = DB::pdo()->prepare("SELECT 1 FROM `Stamp` WHERE id = ? AND user_id = ? LIMIT 1");
            $chk->execute([$id, $userId]);
            return (bool)$chk->fetchColumn();
        }

        return true;
    }

    public function delete(int $id): bool
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user']['id'])) {
            return false;
        }

        $userId = $_SESSION['user']['id'];

        // Check if there are auctions with bids - these cannot be deleted
        $chkBids = DB::pdo()->prepare("
            SELECT 1 FROM `Auction` a 
            INNER JOIN `Bid` b ON a.id = b.auction_id 
            WHERE a.stamp_id = ? LIMIT 1
        ");
        $chkBids->execute([$id]);
        if ($chkBids->fetchColumn()) {
            return false; // Cannot delete stamps with auctions that have bids
        }

        // Verify ownership before deletion
        $ownershipCheck = DB::pdo()->prepare("SELECT 1 FROM `Stamp` WHERE id = ? AND user_id = ? LIMIT 1");
        $ownershipCheck->execute([$id, $userId]);
        if (!$ownershipCheck->fetchColumn()) {
            return false; // Stamp doesn't exist or user doesn't own it
        }

        // Delete auctions without bids first (they will be cascaded by the schema)
        // But we do it explicitly for better control
        DB::pdo()->prepare("DELETE FROM `Auction` WHERE stamp_id = ?")->execute([$id]);

        // Delete stamp images
        DB::pdo()->prepare("DELETE FROM `StampImage` WHERE stamp_id = ?")->execute([$id]);

        // Delete the stamp itself (with user ownership constraint)
        $stmt = DB::pdo()->prepare("DELETE FROM `Stamp` WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    private function handleImageUploads(int $stampId, array $files): bool
    {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/stamps/';
        $webPath = '/uploads/stamps/';

        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadSuccess = false;
        $isFirstImage = true;

        foreach ($files['name'] as $index => $fileName) {
            if (empty($fileName) || $files['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff', 'image/svg+xml', 'image/x-icon', 'image/avif'];
            if (!in_array($files['type'][$index], $allowedTypes)) {
                continue;
            }

            // Generate unique filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = 'stamp_' . $stampId . '_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $uniqueFileName;
            $webUrl = $webPath . $uniqueFileName;

            // Move uploaded file
            if (move_uploaded_file($files['tmp_name'][$index], $uploadPath)) {
                // Try to create thumbnail (optional - skip if GD not available)
                try {
                    if (extension_loaded('gd')) {
                        $thumbnailPath = $uploadDir . 'thumb_' . $uniqueFileName;
                        \App\Core\ImageProcessor::createThumbnail($uploadPath, $thumbnailPath, 300, 300);
                    }
                } catch (\Throwable $e) {
                    error_log("Thumbnail creation failed: " . $e->getMessage());
                }

                // Save to database using StampImage model
                $imageId = \App\Models\StampImage::create($stampId, $webUrl, $isFirstImage);

                if ($imageId) {
                    $uploadSuccess = true;
                    $isFirstImage = false;
                }
            }
        }

        return $uploadSuccess;
    }

    public function setMainImage(int $imageId): bool
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user']['id'])) {
            return false;
        }

        $userId = $_SESSION['user']['id'];

        // First, get the stamp_id for this image and verify ownership
        $stmt = DB::pdo()->prepare("
            SELECT si.stamp_id 
            FROM `StampImage` si 
            INNER JOIN `Stamp` s ON si.stamp_id = s.id 
            WHERE si.id = ? AND s.user_id = ?
        ");
        $stmt->execute([$imageId, $userId]);
        $stampId = $stmt->fetchColumn();

        if (!$stampId) {
            return false; // Image doesn't exist or user doesn't own the stamp
        }

        // Set all images of this stamp to not main
        DB::pdo()->prepare("UPDATE `StampImage` SET is_main = 0 WHERE stamp_id = ?")->execute([$stampId]);

        // Set the specific image as main
        $stmt = DB::pdo()->prepare("UPDATE `StampImage` SET is_main = 1 WHERE id = ?");
        $stmt->execute([$imageId]);

        return $stmt->rowCount() > 0;
    }

    public function deleteImage(int $imageId): bool
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user']['id'])) {
            return false;
        }

        $userId = $_SESSION['user']['id'];

        // Get image info before deletion and verify ownership
        $stmt = DB::pdo()->prepare("
            SELECT si.url, si.stamp_id 
            FROM `StampImage` si 
            INNER JOIN `Stamp` s ON si.stamp_id = s.id 
            WHERE si.id = ? AND s.user_id = ?
        ");
        $stmt->execute([$imageId, $userId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            return false; // Image doesn't exist or user doesn't own the stamp
        }

        // Check if this is the last image for the stamp
        $countStmt = DB::pdo()->prepare("SELECT COUNT(*) FROM `StampImage` WHERE stamp_id = ?");
        $countStmt->execute([$image['stamp_id']]);
        $imageCount = $countStmt->fetchColumn();

        if ($imageCount <= 1) {
            return false; // Don't allow deletion of the last image
        }

        // Delete from database
        $deleteStmt = DB::pdo()->prepare("DELETE FROM `StampImage` WHERE id = ?");
        $deleteStmt->execute([$imageId]);

        if ($deleteStmt->rowCount() > 0) {
            // Try to delete the physical file
            $filePath = str_replace('/assets/', 'public/assets/', $image['url']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // If we deleted the main image, set another image as main
            $checkMain = DB::pdo()->prepare("SELECT COUNT(*) FROM `StampImage` WHERE stamp_id = ? AND is_main = 1");
            $checkMain->execute([$image['stamp_id']]);

            if ($checkMain->fetchColumn() == 0) {
                // No main image exists, set the first remaining image as main
                $setMainStmt = DB::pdo()->prepare("
                    UPDATE `StampImage` 
                    SET is_main = 1 
                    WHERE stamp_id = ? 
                    ORDER BY id ASC 
                    LIMIT 1
                ");
                $setMainStmt->execute([$image['stamp_id']]);
            }

            return true;
        }

        return false;
    }
}
