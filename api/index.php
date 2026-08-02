<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

try {
    // Setup writable storage directories in /tmp for Vercel serverless environment
    $storageDirs = [
        '/tmp/storage',
        '/tmp/storage/app',
        '/tmp/storage/app/public',
        '/tmp/storage/framework',
        '/tmp/storage/framework/cache',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/testing',
        '/tmp/storage/framework/views',
        '/tmp/storage/logs',
        '/tmp/bootstrap/cache',
    ];

    foreach ($storageDirs as $dir) {
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    // Register the Composer autoloader...
    require __DIR__ . '/../vendor/autoload.php';

    // Bootstrap Laravel...
    /** @var Application $app */
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Force storage path to /tmp/storage (writable in Vercel serverless environment)
    $app->useStoragePath('/tmp/storage');

    // Handle the incoming request
    $request = Request::capture();
    $app->handleRequest($request);
} catch (\Throwable $e) {
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Deployment Diagnostic Error</title>";
    echo "<style>body{font-family:sans-serif;padding:2rem;background:#111;color:#eee;} pre{background:#222;padding:1rem;overflow:auto;border-radius:4px;color:#f87171;}</style></head><body>";
    echo "<h2>Server Error Details during Vercel Execution</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</body></html>";
}


