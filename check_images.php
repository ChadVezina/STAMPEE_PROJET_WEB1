<?php
require 'vendor/autoload.php';
require 'app/Core/Config.php';
require 'app/Core/DB.php';

use App\Core\DB;

try {
    $pdo = DB::pdo();
    $stmt = $pdo->query('SELECT stamp_id, url, is_main FROM StampImage LIMIT 5');
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Sample StampImage data:\n";
    foreach ($images as $img) {
        echo "Stamp ID: {$img['stamp_id']}, URL: {$img['url']}, Is Main: {$img['is_main']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
