<?php

/**
 * Laravel Router for Wasmer Edge
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri) && !is_dir(__DIR__.'/public'.$uri)) {
    return false;
}

try {
    require_once __DIR__.'/public/index.php';
} catch (\Throwable $e) {
    http_response_code(200);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Caught Exception on Wasmer Edge:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
