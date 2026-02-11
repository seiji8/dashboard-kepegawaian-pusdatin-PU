<?php
use App\Models\DashboardTracker;

$trackers = DashboardTracker::where('kategori', 'KGB')->get();

$sorted = $trackers->sortBy(function($item) {
    switch ($item->status_saat_ini) {
        case 'Usulan': return 1;
        case 'Upload E-HRM': return 2;
        case 'Proses': return 3;
        default: return 4;
    }
});

foreach ($sorted as $t) {
    echo $t->status_saat_ini . " (" . $t->pegawai_id . ")\n";
}
