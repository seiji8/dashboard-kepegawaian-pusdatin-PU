<?php

use Illuminate\Support\Facades\DB;
use App\Models\DashboardTracker;

$duplicates = DB::table('dashboard_tracker')
    ->select('pegawai_id', 'kategori', DB::raw('MIN(id) as min_id'), DB::raw('COUNT(*) as count'))
    ->groupBy('pegawai_id', 'kategori')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($duplicates as $duplicate) {
    // Keep the one with min_id, delete the rest for this pegawai_id and kategori
    DashboardTracker::where('pegawai_id', $duplicate->pegawai_id)
        ->where('kategori', $duplicate->kategori)
        ->where('id', '!=', $duplicate->min_id)
        ->delete();
    
    echo "Deleted duplicates for Pegawai ID: {$duplicate->pegawai_id}, Kategori: {$duplicate->kategori}\n";
}

echo "Cleanup completed!\n";
