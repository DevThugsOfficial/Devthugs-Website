<?php

header('Content-Type: text/plain; charset=utf-8');

echo "PHP OK\n";
echo 'version=' . PHP_VERSION . "\n";
echo 'vercel=' . (getenv('VERCEL') !== false ? 'yes' : 'no') . "\n";
echo 'vendor=' . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'yes' : 'no') . "\n";
echo 'public=' . (file_exists(__DIR__ . '/../public/index.php') ? 'yes' : 'no') . "\n";
