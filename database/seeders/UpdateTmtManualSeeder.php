<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class UpdateTmtManualSeeder extends Seeder
{
    public function run()
    {
        // Daftar Pegawai dan TMT Manualnya
        $dataManual = [
            // Kosongkan agar data asli dari API (2026) tidak di-overwrite menjadi 2024
        ];

        foreach ($dataManual as $nip => $tmt) {
            Pegawai::where('nip', $nip)->update(['tmt_kgb_terakhir' => $tmt]);
        }
    }
}
