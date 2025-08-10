<?php

/**
 * Database configuration
 */

$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'stampee';
$port = 3307;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Connection réussie
    echo "Connected successfully to the database.";
} catch (PDOException $e) {
    // En cas d'erreur de connexion
    echo "Connection failed: " . $e->getMessage();
}
