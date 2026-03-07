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

            '199003212014021001' => '2024-02-01',
            // Tambah NIP lain di sini...
        ];
        
        foreach ($dataManual as $nip => $tmt) {
            Pegawai::where('nip', $nip)->update(['tmt_kgb_terakhir' => $tmt]);
        }
    }
}