<?php

declare(strict_types=1);

namespace App\Core;

final class Storage
{
    private const UPLOAD_DIR = '/storage/media';
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    public static function uploadFile(array $file, string $subdirectory = 'stamps'): ?string
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        // Validate file type
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, self::ALLOWED_TYPES)) {
            return null;
        }

        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return null;
        }

        // Create upload directory if it doesn't exist
        $uploadPath = self::getStoragePath() . '/' . $subdirectory;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $fullPath = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return self::UPLOAD_DIR . '/' . $subdirectory . '/' . $filename;
        }

        return null;
    }

    public static function deleteFile(string $relativePath): bool
    {
        $fullPath = self::getStoragePath() . str_replace(self::UPLOAD_DIR, '', $relativePath);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    public static function getStoragePath(): string
    {
        return defined('BASE_PATH') ? BASE_PATH . self::UPLOAD_DIR : dirname(__DIR__, 2) . self::UPLOAD_DIR;
    }

    public static function fileExists(string $relativePath): bool
    {
        $fullPath = self::getStoragePath() . str_replace(self::UPLOAD_DIR, '', $relativePath);
        return file_exists($fullPath);
    }
}
