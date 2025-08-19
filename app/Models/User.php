<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = DB::pdo()->prepare("SELECT id, nom, email, password FROM `User` WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(string $nom, string $email, string $hash): bool
    {
        $stmt = DB::pdo()->prepare("INSERT INTO `User` (nom, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$nom, $email, $hash]);
    }

    public static function findById(int $id): ?array
    {
        $stmt = DB::pdo()->prepare("SELECT * FROM `User` WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function updateEmail(int $id, string $email): bool
    {
        $stmt = DB::pdo()->prepare("UPDATE `User` SET email = ? WHERE id = ?");
        return $stmt->execute([$email, $id]);
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        $stmt = DB::pdo()->prepare("UPDATE `User` SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public static function deleteById(int $id): bool
    {
        $stmt = DB::pdo()->prepare("DELETE FROM `User` WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
