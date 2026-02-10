<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Pegawai;

class UpdateTmtManualSeeder extends Seeder
{
    public function run()
    {
        // Daftar Pegawai dan TMT Manualnya
        $dataManual = [
            '197511272008121001' => '2024-04-01', // NIP => TMT
            // Tambah NIP lain di sini...
        ];

        foreach ($dataManual as $nip => $tmt) {
            Pegawai::where('nip', $nip)->update(['tmt_kgb_terakhir' => $tmt]);
        }
    }
}