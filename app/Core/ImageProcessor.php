<?php

declare(strict_types=1);

namespace App\Core;

final class ImageProcessor
{
    public static function createThumbnail(string $sourcePath, string $destinationPath, int $maxWidth = 300, int $maxHeight = 300): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [$width, $height, $type] = $imageInfo;

        // Create source image resource
        $sourceImage = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => null
        };

        if (!$sourceImage) {
            return false;
        }

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }

        // Resize image
        imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Create destination directory if it doesn't exist
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // Save thumbnail
        $success = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($thumbnail, $destinationPath, 85),
            IMAGETYPE_PNG => imagepng($thumbnail, $destinationPath),
            IMAGETYPE_GIF => imagegif($thumbnail, $destinationPath),
            IMAGETYPE_WEBP => imagewebp($thumbnail, $destinationPath, 85),
            default => false
        };

        // Cleanup
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);

        return $success;
    }

    public static function optimizeImage(string $sourcePath, string $destinationPath = null, int $quality = 85): bool
    {
        $destination = $destinationPath ?? $sourcePath;

        if (!file_exists($sourcePath)) {
            return false;
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [,, $type] = $imageInfo;

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => null
        };

        if (!$image) {
            return false;
        }

        $success = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $destination, $quality),
            IMAGETYPE_PNG => imagepng($image, $destination, (int)(9 - ($quality / 10))),
            IMAGETYPE_WEBP => imagewebp($image, $destination, $quality),
            default => false
        };

        imagedestroy($image);
        return $success;
    }
}
