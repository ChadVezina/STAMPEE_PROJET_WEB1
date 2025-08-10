<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\DB;
use App\Core\CsrfToken;
use Dotenv\Dotenv;

define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/src/views');

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Toronto');

Config::set('app.base_url', $_ENV['BASE_URL'] ?? 'http://localhost/stampee');

session_save_path(BASE_PATH . '/' . ($_ENV['SESSION_PATH'] ?? 'storage/sessions'));
if (!is_dir(session_save_path())) {
    mkdir(session_save_path(), 0777, true);
}
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// Prépare la connexion PDO (lazy dans DB::pdo()) et CSRF
DB::init([
    'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port'    => $_ENV['DB_PORT'] ?? '3306',
    'dbname'  => $_ENV['DB_NAME'] ?? 'Stampee',
    'user'    => $_ENV['DB_USER'] ?? 'root',
    'pass'    => $_ENV['DB_PASS'] ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
]);
CsrfToken::boot();
