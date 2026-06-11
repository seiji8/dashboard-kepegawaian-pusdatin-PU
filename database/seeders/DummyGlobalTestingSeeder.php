<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\RiwayatSkp;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyGlobalTestingSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::now();
        
        // Bersihkan dummy tracker
        DashboardTracker::whereIn('pegawai_id', ['TEST-KP-001', 'TEST-KP-002'])->delete();

        // Delete existing pegawais first to avoid FK constraint errors on primary key update
        Pegawai::where('nip', '199001012015011001')->delete();
        Pegawai::where('nip', '199202022018021002')->delete();

        // 1. Pegawai 1: ezaadityan@gmail.com (Untuk Test KP Reguler/Fungsional - Kekurangan SKP)
        $eza = Pegawai::updateOrCreate(
            ['nip' => '199001012015011001'],
            [
                'email' => 'ezaadityan@gmail.com',
                'id_pegawai_api' => 'TEST-KP-001',
                'nama' => 'Eza Adityan',
                'tipe_jabatan' => 'Pelaksana',
                'pangkat_golongan' => 'III/a',
                'tmt_pangkat_terakhir' => $today->copy()->subYears(4)->subDays(1)->format('Y-m-d'), // Udah 4 tahun, harusnya bisa Usulan KP Reguler
                'jabatan_saat_ini' => 'Pengolah Data',
            ]
        );

        // Berikan SKP buruk untuk Eza agar tertahan di 'Aman' dengan 'Kurang SKP'
        RiwayatSkp::updateOrCreate(
            ['nip' => $eza->nip, 'tahun' => $today->year - 1],
            ['status' => 'Tahunan', 'nilai_skp' => 'CUKUP', 'arsip_skp' => 'dummy.pdf']
        );
        RiwayatSkp::updateOrCreate(
            ['nip' => $eza->nip, 'tahun' => $today->year - 2],
            ['status' => 'Tahunan', 'nilai_skp' => 'BAIK', 'arsip_skp' => 'dummy.pdf']
        );

        // 2. Pegawai 2: sanfaedloni@students.unnes.ac.id (Untuk Test KJ/UKOM/Upload E-HRM)
        $san = Pegawai::updateOrCreate(
            ['nip' => '199202022018021002'],
            [
                'email' => 'sanfaedloni@students.unnes.ac.id',
                'id_pegawai_api' => 'TEST-KP-002',
                'nama' => 'San Faedloni',
                'tipe_jabatan' => 'Fungsional',
                'jenjang' => 'Ahli Pertama',
                'pangkat_golongan' => 'III/b',
                'tmt_pangkat_terakhir' => $today->copy()->subYears(2)->format('Y-m-d'),
                'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            ]
        );

        // Berikan SKP Baik untuk San
        RiwayatSkp::updateOrCreate(
            ['nip' => $san->nip, 'tahun' => $today->year - 1],
            ['status' => 'Tahunan', 'nilai_skp' => 'BAIK', 'arsip_skp' => 'dummy.pdf']
        );
        RiwayatSkp::updateOrCreate(
            ['nip' => $san->nip, 'tahun' => $today->year - 2],
            ['status' => 'Tahunan', 'nilai_skp' => 'BAIK', 'arsip_skp' => 'dummy.pdf']
        );

        // Set San ke Upload E-HRM untuk KP Struktural sebagai tes dinamis dokumen
        // Walau dia Fungsional, kita tembak paksa Trackernya untuk ngetes Upload E-HRM
        DashboardTracker::updateOrCreate(
            ['pegawai_id' => $san->id_pegawai_api, 'kategori' => 'KP_Struktural'],
            [
                'status_saat_ini' => 'Upload E-HRM',
                'keterangan' => 'Simulasi Upload E-HRM',
                'dokumen_total' => 2,
                'dokumen_terupload' => 0,
            ]
        );
    }
}
