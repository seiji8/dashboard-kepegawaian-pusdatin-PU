<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$schema = \Illuminate\Support\Facades\Schema::getColumnListing('riwayat_angka_kredit');
echo json_encode($schema);
