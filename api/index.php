<?php

/**
 * Vercel serverless entry point for Laravel.
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
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
            mkdir($dir, 0777, true);
        }
    }

    $dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
    if (is_string($dbPath) && str_ends_with($dbPath, '.sqlite') && !file_exists($dbPath)) {
        touch($dbPath);
    }

    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoload)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Laravel vendor/autoload.php is missing. Composer dependencies were not installed on Vercel.\n";
        echo "Checked path: {$autoload}\n";
        exit(1);
    }

    require __DIR__ . '/../public/index.php';
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Vercel Laravel boot error\n\n";
    echo $e::class . ': ' . $e->getMessage() . "\n\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
}
