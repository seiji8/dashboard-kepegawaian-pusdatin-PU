<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\DashboardTracker;

class CleanUkomDummySeeder extends Seeder
{
    public function run(): void
    {
        $ids = ['DUMMY-UKOM-001', 'DUMMY-UKOM-002'];

        // Hapus tracker terlebih dahulu (foreign key)
        DashboardTracker::whereIn('pegawai_id', $ids)->delete();

        // Hapus pegawai dummy
        Pegawai::whereIn('id_pegawai_api', $ids)->delete();

        $this->command->info('🗑️  Data dummy UKOM berhasil dihapus!');
    }
}
