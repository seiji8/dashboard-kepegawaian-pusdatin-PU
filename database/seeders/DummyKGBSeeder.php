<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use Carbon\Carbon;

class DummyKGBSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan tracker KGB test sebelumnya
        DashboardTracker::whereIn('pegawai_id', ['TEST-KGB-001', 'TEST-KGB-002'])->delete();

        $dummies = [
            [
                'id_pegawai_api' => 'TEST-KGB-001',
                'nip' => '199010102015031001',
                'nama' => 'Hasan',
                'email' => 'hasan.inf1re7@gmail.com',
                'jabatan_saat_ini' => 'Analis Sistem Informasi',
                'tipe_jabatan' => 'Fungsional',
                'pangkat_golongan' => 'Penata (III/c)',
                'tmt_kgb_terakhir' => Carbon::now()->subYears(2)->addDays(30)->format('Y-m-d'), // KGB 30 hari lagi -> Usulan
            ],
            [
                'id_pegawai_api' => 'TEST-KGB-002',
                'nip' => '199010102015031002',
                'nama' => 'Raissa',
                'email' => 'raissaakhdiyan@gmail.com',
                'jabatan_saat_ini' => 'Analis Keuangan',
                'tipe_jabatan' => 'Fungsional',
                'pangkat_golongan' => 'Penata Muda Tk. I (III/b)',
                'tmt_kgb_terakhir' => Carbon::now()->subYears(2)->subDays(5)->format('Y-m-d'), // KGB 5 hari yang lalu
            ],
        ];

        foreach ($dummies as $index => $data) {
            $pegawai = Pegawai::updateOrCreate(
                ['nip' => $data['nip']],
                $data
            );

            if ($data['id_pegawai_api'] === 'TEST-KGB-002') {
                // Untuk Raissa, kita force statusnya jadi Upload E-HRM agar sistem tidak mengubahnya kembali ke Usulan
                // dan email Upload E-HRM bisa terkirim
                DashboardTracker::updateOrCreate(
                    [
                        'pegawai_id' => $pegawai->id_pegawai_api,
                        'kategori' => 'KGB',
                    ],
                    [
                        'status_saat_ini' => 'Upload E-HRM',
                        'tanggal_target' => Carbon::now()->subDays(5)->format('Y-m-d'),
                        'dokumen_total' => 1,
                        'dokumen_terupload' => 0,
                        'keterangan' => 'TTE Selesai. Menunggu upload SK E-HRM.',
                        // Kosongkan notified_at agar notifikasi bisa masuk (atau akan di set null jika status berubah, tapi ini force)
                        'notified_at' => null,
                    ]
                );
            }
        }
    }
}
