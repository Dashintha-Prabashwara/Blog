<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Strip /Blog prefix if accessed via /Blog/...
if (str_starts_with($uri, '/Blog')) {
    $uri = substr($uri, 5);
}

if ($uri === '' || $uri === '/') {
    $uri = '/index.php';
}

$file = __DIR__ . $uri;

if (file_exists($file) && !is_dir($file)) {
    // Let PHP server handle static assets directly
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
        return false;
    }
    require $file;
    exit;
}

if (is_dir($file) && file_exists($file . '/index.php')) {
    require $file . '/index.php';
    exit;
}

http_response_code(404);
require __DIR__ . '/404.php';
