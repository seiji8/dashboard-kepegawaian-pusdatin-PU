<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$counts = \App\Models\Pegawai::select('tipe_jabatan', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->groupBy('tipe_jabatan')
    ->get();

foreach($counts as $t) {
    echo ($t->tipe_jabatan ?: 'NULL') . " : " . $t->count . PHP_EOL;
}
