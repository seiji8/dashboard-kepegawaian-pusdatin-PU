<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$p = App\Models\Pegawai::where('nip', '198302232010121003')->first();
echo json_encode([
    'skp' => $p->arsip_skp_2_tahun,
    'count' => count($p->arsip_skp_2_tahun ?? []),
    'empty' => empty($p->arsip_skp_2_tahun)
]);
