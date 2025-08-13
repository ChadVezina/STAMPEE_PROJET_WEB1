<?php

declare(strict_types=1);

// Debug: Log what index.php receives
error_log("=== INDEX.PHP DEBUG ===");
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NULL'));
error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NULL'));

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../boot/app.php';

// Routes
require __DIR__ . '/../routes/web.php';

// Dispatch routes
\App\Routes\Route::dispatch();
