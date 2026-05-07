<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use Carbon\Carbon;

class DummyStructuralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama untuk testing
        Pegawai::where('nama', 'LIKE', 'Dummy Struct%')->delete();

        $data = [
            [
                'id_pegawai_api' => 'dummy-struct-1',
                'nip' => '199001012015011001',
                'nama' => 'Dummy Struct Case 2 (Immediate)',
                'email' => 'struct1@example.com',
                'kd_eselon' => '5', // III/a
                'pangkat_golongan' => 'III/a',
                'tipe_jabatan' => 'Struktural',
                'tmt_pangkat_terakhir' => '2022-04-01', // 4 tahun lalu
                'tmt_struktural' => '2025-11-01', // Baru diangkat 6 bulan lalu
            ],
            [
                'id_pegawai_api' => 'dummy-struct-2',
                'nip' => '199001012015011002',
                'nama' => 'Dummy Struct Case 1 (Not in Window)',
                'email' => 'struct2@example.com',
                'kd_eselon' => '5',
                'pangkat_golongan' => 'III/a',
                'tipe_jabatan' => 'Struktural',
                'tmt_pangkat_terakhir' => '2024-04-01', // 2 tahun lalu
                'tmt_struktural' => '2025-11-01', // Baru diangkat 6 bulan lalu (Target: Nov 2026)
            ],
            [
                'id_pegawai_api' => 'dummy-struct-3',
                'nip' => '199001012015011003',
                'nama' => 'Dummy Struct Case 1 (In Window)',
                'email' => 'struct3@example.com',
                'kd_eselon' => '5',
                'pangkat_golongan' => 'III/a',
                'tipe_jabatan' => 'Struktural',
                'tmt_pangkat_terakhir' => '2024-04-01', // 2 tahun lalu
                'tmt_struktural' => '2025-07-01', // Target: July 2026 (Within 60 days from May 2026)
            ],
            [
                'id_pegawai_api' => 'dummy-struct-4',
                'nip' => '199001012015011004',
                'nama' => 'Dummy Struct Case 3 (Reguler In Window)',
                'email' => 'struct4@example.com',
                'kd_eselon' => '5',
                'pangkat_golongan' => 'III/a',
                'tipe_jabatan' => 'Struktural',
                'tmt_pangkat_terakhir' => '2022-06-01', // Target: June 2026 (Within 60 days)
                'tmt_struktural' => '2022-04-01', // Pelantikan sudah lama (bukan appointment baru)
            ],
            [
                'id_pegawai_api' => 'dummy-struct-5',
                'nip' => '199001012015011005',
                'nama' => 'Dummy Struct Case Peak Pangkat',
                'email' => 'struct5@example.com',
                'kd_eselon' => '5', 
                'pangkat_golongan' => 'IV/b', // Already peak for Eselon 5
                'tipe_jabatan' => 'Struktural',
                'tmt_pangkat_terakhir' => '2022-01-01',
                'tmt_struktural' => '2021-01-01',
            ],
        ];

        foreach ($data as $d) {
            Pegawai::create($d);
        }

        $this->command->info('Dummy structural data created successfully!');
    }
}
