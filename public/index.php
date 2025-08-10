<?php

declare(strict_types=1);

use App\Core\Router;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../boot/app.php';

// Routes
require __DIR__ . '/../routes/web.php';

// Démarre le routeur
Router::dispatch();
