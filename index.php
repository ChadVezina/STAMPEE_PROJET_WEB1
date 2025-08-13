<?php

declare(strict_types=1);

// Front controller à la racine
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/boot/app.php';
require __DIR__ . '/routes/web.php';

// Dispatch routes
\App\Routes\Route::dispatch();
