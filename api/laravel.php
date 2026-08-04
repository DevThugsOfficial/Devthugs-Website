<?php

/**
 * Laravel entry used by the Node PHP bridge on Vercel.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', '1');
ini_set('log_errors', '1');

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
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

define('LARAVEL_START', microtime(true));

try {
    $autoload = __DIR__.'/../vendor/autoload.php';
    if (! file_exists($autoload)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Missing vendor/autoload.php\n";
        exit;
    }

    require $autoload;

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');
    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Laravel bootstrap failed:\n";
    echo $e->getMessage()."\n\n";
    echo $e->getFile().':'.$e->getLine()."\n\n";
    echo $e->getTraceAsString();
}
