<?php

/**
 * Vercel serverless entry point for Laravel.
 */

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
        @mkdir($dir, 0777, true);
    }
}

require __DIR__ . '/../public/index.php';
