<?php

/**
 * Vercel serverless entry point for Laravel.
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

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
    '/tmp/views',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Ensure sqlite file exists when using DB_CONNECTION=sqlite on Vercel
$dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
if (is_string($dbPath) && str_ends_with($dbPath, '.sqlite') && !file_exists($dbPath)) {
    @touch($dbPath);
}

require __DIR__ . '/../public/index.php';
