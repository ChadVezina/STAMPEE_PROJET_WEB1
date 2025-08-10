<?php

namespace App\Core;

use PDO;
use PDOException;

final class DB
{
    private static array $cfg;
    private static ?PDO $pdo = null;

    public static function init(array $config): void
    {
        self::$cfg = $config;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            self::$cfg['host'],
            self::$cfg['port'],
            self::$cfg['dbname'],
            self::$cfg['charset']
        );
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            self::$pdo = new PDO($dsn, self::$cfg['user'], self::$cfg['pass'], $opt);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Erreur DB: ' . $e->getMessage());
        }
        return self::$pdo;
    }
}
