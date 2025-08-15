<?php

declare(strict_types=1);

// Redirect all requests to the public directory
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// If already in public directory, prevent redirect loop
if (strpos($requestUri, '/public/') === 0) {
    http_response_code(404);
    echo '404 - Page not found';
    exit;
}

// Clean the request URI
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

// Remove base path if present (for subdirectory installations)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

// Build the public URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$publicUrl = $protocol . '://' . $host . $basePath . '/public' . $path;

// Add query string if present
if (!empty($_SERVER['QUERY_STRING'])) {
    $publicUrl .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $publicUrl);
exit;
