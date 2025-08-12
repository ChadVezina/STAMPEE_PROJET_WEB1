<?php

// Router for PHP built-in server to handle static assets and application routes

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Compute base path from the script location (works whether app is in subfolder like /stampee)
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(dirname($scriptName), '/');
$basePath = $scriptDir === '/' ? '' : $scriptDir;
if ($basePath && strpos($uri, $basePath . '/') === 0) {
    $uri = substr($uri, strlen($basePath));
} elseif ($basePath && $uri === $basePath) {
    $uri = '/';
}

// Fallback for common local setup where app lives under /stampee
if (strpos($uri, '/stampee/') === 0) {
    $uri = substr($uri, strlen('/stampee'));
} elseif ($uri === '/stampee') {
    $uri = '/';
}

// Handle static assets (css, js, images, etc.)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) {
        // Set appropriate content type
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];

        if (isset($contentTypes[$ext])) {
            header('Content-Type: ' . $contentTypes[$ext]);
        }

        readfile($file);
        return true;
    }
    // Return 404 for missing static files
    http_response_code(404);
    echo "File not found: $uri";
    return false;
}

// For non-static files, use the main application
// Normalize superglobals so the app sees the trimmed URI
$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['PATH_INFO'] = $uri;
require_once __DIR__ . '/index.php';
