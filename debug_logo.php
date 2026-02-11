<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$path = public_path('assets/Logo_PU.png');
echo "Resolved Path: " . $path . "\n";

if (file_exists($path)) {
    echo "File Exists: YES\n";
    $content = file_get_contents($path);
    if ($content === false) {
        echo "file_get_contents: FAILED\n";
    } else {
        echo "file_get_contents: SUCCESS (" . strlen($content) . " bytes)\n";
        echo "Base64 (first 20 chars): " . substr(base64_encode($content), 0, 20) . "\n";
    }
} else {
    echo "File Exists: NO\n";
}
