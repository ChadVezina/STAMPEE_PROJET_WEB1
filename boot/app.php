<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\DB;
use App\Core\CsrfToken;
use Dotenv\Dotenv;

define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/ressources/views');

// .env
if (file_exists(BASE_PATH . '/.env')) {
  Dotenv::createImmutable(BASE_PATH)->load();
}
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Montreal');

// Base path/URL (neutralise /public)
$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')); // /stampee/public
$basePath  = rtrim(preg_replace('#/public$#', '', $scriptDir), '/');         // /stampee ou ''
$baseUrl   = $scheme . '://' . $host . $basePath;
Config::set('app.base_path', $basePath);
Config::set('app.base_url',  $baseUrl);

// Sessions
$sessionPath = $_ENV['SESSION_PATH'] ?? 'storage/sessions';
$save = BASE_PATH . '/' . $sessionPath;
if (!is_dir($save)) {
  if (!mkdir($save, 0777, true)) {
    throw new RuntimeException("Failed to create session directory: $save");
  }
}
session_save_path($save);
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// DB (définis $appEnv et valeurs locales standard)
$appEnv = $_ENV['APP_ENV'] ?? 'local';
DB::init([
  'host'    => $_ENV['DB_HOST'],
  'port'    => $_ENV['DB_PORT'],
  'dbname'  => $_ENV['DB_NAME'],
  'user'    => $_ENV['DB_USER'],
  'pass'    => $_ENV['DB_PASS'],
  'charset' => $_ENV['DB_CHARSET']
]);
CsrfToken::boot();
