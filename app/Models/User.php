<?php
namespace App\Models;

use App\Core\DB;
use PDO;

final class User {
    public static function findByEmail(string $email): ?array {
        $stmt = DB::pdo()->prepare("SELECT id, nom, email, password FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(string $nom, string $email, string $hash): bool {
        $stmt = DB::pdo()->prepare("INSERT INTO User (nom, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$nom, $email, $hash]);
    }
}