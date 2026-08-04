<?php

header('Content-Type: application/json');

echo json_encode([
    'ok' => true,
    'php' => PHP_VERSION,
    'vercel' => getenv('VERCEL') !== false,
    'vendor' => file_exists(__DIR__ . '/../vendor/autoload.php'),
    'public' => file_exists(__DIR__ . '/../public/index.php'),
    'composer' => file_exists(__DIR__ . '/../composer.json'),
    'cwd' => getcwd(),
    'dir' => __DIR__,
], JSON_PRETTY_PRINT);
