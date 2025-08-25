<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = DB::pdo()->prepare("SELECT id, nom, email, password FROM `user` WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(string $nom, string $email, string $hash): bool
    {
        $stmt = DB::pdo()->prepare("INSERT INTO `user` (nom, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$nom, $email, $hash]);
    }

    public static function findById(int $id): ?array
    {
        $stmt = DB::pdo()->prepare("SELECT * FROM `user` WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function updateEmail(int $id, string $email): bool
    {
        $stmt = DB::pdo()->prepare("UPDATE `user` SET email = ? WHERE id = ?");
        return $stmt->execute([$email, $id]);
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        $stmt = DB::pdo()->prepare("UPDATE `user` SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public static function deleteById(int $id): bool
    {
        try {
            // Commencer une transaction
            DB::pdo()->beginTransaction();

            // Supprimer d'abord toutes les données liées
            // 1. Supprimer les offres de l'utilisateur
            $stmt = DB::pdo()->prepare("DELETE FROM `bid` WHERE user_id = ?");
            $stmt->execute([$id]);

            // 2. Supprimer les favoris de l'utilisateur
            $stmt = DB::pdo()->prepare("DELETE FROM `favorite` WHERE user_id = ?");
            $stmt->execute([$id]);

            // 3. Supprimer les enchères où l'utilisateur est vendeur
            $stmt = DB::pdo()->prepare("DELETE FROM `auction` WHERE seller_id = ?");
            $stmt->execute([$id]);

            // 4. Supprimer les images des timbres de l'utilisateur
            $stmt = DB::pdo()->prepare("DELETE si FROM `stampimage` si INNER JOIN `stamp` s ON si.stamp_id = s.id WHERE s.user_id = ?");
            $stmt->execute([$id]);

            // 5. Supprimer les timbres de l'utilisateur
            $stmt = DB::pdo()->prepare("DELETE FROM `stamp` WHERE user_id = ?");
            $stmt->execute([$id]);

            // 6. Enfin, supprimer l'utilisateur
            $stmt = DB::pdo()->prepare("DELETE FROM `user` WHERE id = ?");
            $stmt->execute([$id]);
            $success = $stmt->rowCount() > 0;

            // Valider la transaction
            DB::pdo()->commit();
            return $success;
        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            DB::pdo()->rollBack();
            error_log("Erreur lors de la suppression de l'utilisateur {$id}: " . $e->getMessage());
            return false;
        }
    }
}
