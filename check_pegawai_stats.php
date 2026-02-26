<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pegawais = \App\Models\Pegawai::count();
$akRecords = \App\Models\RiwayatAngkaKredit::count();
$uniqueAk = \App\Models\RiwayatAngkaKredit::distinct('id_pegawai_api')->count('id_pegawai_api');

echo "Pegawais: $pegawais \nAK Records: $akRecords \nUnique Users w/ AK: $uniqueAk\n";

// Mari kita periksa Jenjang Pegawai dari API eHRM
$jenjangDist = \App\Models\Pegawai::select('jenjang', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->groupBy('jenjang')
    ->get();

echo "Jenjang Distribution:\n";
foreach($jenjangDist as $j) {
    echo "- " . ($j->jenjang ?: 'NULL') . " : " . $j->count . "\n";
}

$tracker = \App\Models\DashboardTracker::select('kategori', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->groupBy('kategori')
    ->get();

echo "Tracker Distribution:\n";
foreach($tracker as $t) {
    echo "- " . $t->kategori . " : " . $t->count . "\n";
}
