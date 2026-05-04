<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use Carbon\Carbon;

class DummyKJSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dummies = [
            [
                'id_pegawai_api' => 'KJ-DUMMY-001',
                'nip' => '198001012005011001',
                'nama' => 'Budi Santoso',
                'jabatan_saat_ini' => 'Analis Sistem Informasi',
                'tipe_jabatan' => 'Fungsional',
                'pangkat_golongan' => 'Penata (III/c)',
                'jenjang' => 'Ahli Muda',
            ],
            [
                'id_pegawai_api' => 'KJ-DUMMY-002',
                'nip' => '198505052010122002',
                'nama' => 'Siti Aminah',
                'jabatan_saat_ini' => 'Analis Keuangan',
                'tipe_jabatan' => 'Fungsional',
                'pangkat_golongan' => 'Penata Muda Tk. I (III/b)',
                'jenjang' => 'Ahli Pertama',
            ],
            [
                'id_pegawai_api' => 'KJ-DUMMY-003',
                'nip' => '199010102015031003',
                'nama' => 'Agus Pratama',
                'jabatan_saat_ini' => 'Pranata Komputer',
                'tipe_jabatan' => 'Fungsional',
                'pangkat_golongan' => 'Pengatur Tk. I (II/d)',
                'jenjang' => 'Terampil',
            ],
        ];

        foreach ($dummies as $index => $data) {
            $pegawai = Pegawai::updateOrCreate(
                ['nip' => $data['nip']],
                $data
            );

            // Buat entri di DashboardTracker untuk KJ_Jafung
            DashboardTracker::updateOrCreate(
                [
                    'pegawai_id' => $pegawai->id_pegawai_api,
                    'kategori' => 'KJ_Jafung',
                ],
                [
                    'status_saat_ini' => 'Usulan',
                    'tanggal_target' => Carbon::now()->addDays(30)->format('Y-m-d'),
                    'dokumen_total' => 4,
                    'dokumen_terupload' => 0,
                    'keterangan' => 'Dummy usulan KJ Jafung ke-' . ($index + 1),
                ]
            );
        }
    }
}
