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
    ];

    foreach ($storageDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    $autoload = __DIR__ . '/../vendor/autoload.php';
    $publicIndex = __DIR__ . '/../public/index.php';

    if (!file_exists($autoload)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Missing vendor/autoload.php\n";
        echo "Composer dependencies were not installed on Vercel.\n";
        echo "Path checked: {$autoload}\n";
        exit(1);
    }

    if (!file_exists($publicIndex)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Missing public/index.php\n";
        echo "Path checked: {$publicIndex}\n";
        exit(1);
    }

    require $publicIndex;
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Laravel boot failed on Vercel\n\n";
    echo $e::class . ': ' . $e->getMessage() . "\n\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
}
