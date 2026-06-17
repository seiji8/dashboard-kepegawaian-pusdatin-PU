<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tipeJabatan = App\Models\Pegawai::distinct()->pluck('tipe_jabatan')->toArray();
echo "Distinct tipe_jabatan:\n";
print_r($tipeJabatan);

$samplePegawai = App\Models\Pegawai::first();
if ($samplePegawai) {
    echo "Sample Pegawai Data:\n";
    print_r($samplePegawai->toArray());
}
