<?php
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use Illuminate\Support\Facades\Schema;

// Create dummy pegawai with minimal required fields
// We need to know what columns are nullable or required. 
// Assuming factory or basic create.

// Let's rely on updateOrCreate with ID
$p1 = Pegawai::updateOrCreate(
    ['id_pegawai_api' => 9991],
    ['nama' => 'Test Sort Usulan', 'nip' => '9991']
);

$p2 = Pegawai::updateOrCreate(
    ['id_pegawai_api' => 9992],
    ['nama' => 'Test Sort Upload', 'nip' => '9992']
);

$p3 = Pegawai::updateOrCreate(
    ['id_pegawai_api' => 9993],
    ['nama' => 'Test Sort Proses', 'nip' => '9993']
);

// Create Trackers
// 1. Usulan
DashboardTracker::updateOrCreate(
    ['pegawai_id' => 9991, 'kategori' => 'KGB'],
    ['status_saat_ini' => 'Usulan', 'tanggal_target' => '2026-04-01', 'keterangan' => 'Test Usulan']
);

// 2. Upload E-HRM
DashboardTracker::updateOrCreate(
    ['pegawai_id' => 9992, 'kategori' => 'KGB'],
    ['status_saat_ini' => 'Upload E-HRM', 'tanggal_target' => '2026-04-01', 'keterangan' => 'Test Upload']
);

// 3. Proses
DashboardTracker::updateOrCreate(
    ['pegawai_id' => 9993, 'kategori' => 'KGB'],
    ['status_saat_ini' => 'Proses', 'tanggal_target' => '2026-04-01', 'keterangan' => 'Test Proses', 'dikonfirmasi_at' => now()]
);

echo "Test data created successfully.\n";
