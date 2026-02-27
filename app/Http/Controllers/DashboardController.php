<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\KelengkapanDokumen;
use App\Models\NotifikasiRules; // Added
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. DATA KARTU STATISTIK (Ringkasan)
        $totalPegawai = Pegawai::count();
        
        $tenggatMendesak = DashboardTracker::whereNull('dikonfirmasi_at')
                                       ->where(function($q) {
                                           $q->whereIn('status_saat_ini', ['Usulan', 'Upload E-HRM', 'Mendekati', 'Menunggu SKP']);
                                       })
                                       ->count();
                                           
        // REVISI: 'Usulan' sekarang status warning (merah).
        // Yang sedang "Di-usulkan/Diproses" (kuning) adalah status 'Proses'.
        $jumlahUsulan = DashboardTracker::where('status_saat_ini', 'Proses')->count();

        // Hitung persentase kepatuhan (Dokumen Terupload / Total Dokumen)
        $totalDokumenWajib = DashboardTracker::sum('dokumen_total');
        $totalDokumenAda   = DashboardTracker::sum('dokumen_terupload');
        
        $tingkatKepatuhan = $totalDokumenWajib > 0 
                            ? round(($totalDokumenAda / $totalDokumenWajib) * 100) 
                            : 0;

        // 2. DATA TUGAS (Task List)
        // Kita ambil data tracker dan load relasi pegawainya
        // REVISI KGB: Tampilkan juga yang sudah dikonfirmasi (status 'Proses') jika kategorinya KGB
        $trackers = DashboardTracker::with('pegawai')
                    ->where(function($query) {
                        $query->whereNull('dikonfirmasi_at')
                              ->orWhere('kategori', 'KGB'); 
                    })
                    ->get();

        // Kelompokkan berdasarkan Kategori untuk Accordion
        $listKenaikanPangkat = $trackers->where('kategori', 'KP_Jafung'); // Gabung Struktural & Jafung logic nanti
        $listKenaikanJenjang = $trackers->where('kategori', 'KJ_Jafung'); // Kenaikan Jenjang / UKOM
        
        $listKGB             = $trackers->where('kategori', 'KGB')->sortBy(function($item) {
            switch ($item->status_saat_ini) {
                case 'Usulan': return 1;
                case 'Upload E-HRM': return 2;
                case 'Proses': return 3;
                default: return 4;
            }
        });
        
        // Pisahkan Struktural vs Fungsional berdasarkan tipe_jabatan yang sesungguhnya di database
        $kpStruktural = $listKenaikanPangkat->filter(function($item) {
            return str_contains(strtolower($item->pegawai->tipe_jabatan), 'struktural');
        });

        $kpFungsional = $listKenaikanPangkat->filter(function($item) {
            return str_contains(strtolower($item->pegawai->tipe_jabatan), 'fungsional');
        });

        // Ambil template manual untuk modal reminder di dashboard
        $templates = NotifikasiRules::where('interval_hari', 0)->get();

        return view('dashboard.index', compact(
            'totalPegawai', 
            'tenggatMendesak', 
            'jumlahUsulan', 
            'tingkatKepatuhan',
            'kpStruktural',
            'kpFungsional',
            'listKenaikanJenjang',
            'listKGB',
            'templates'
        ));
    }

/**
 * Konfirmasi manual bahwa tugas KGB sudah diproses di dunia nyata.
 */
public function confirmTracker(Request $request, $id)
{
    $tracker = DashboardTracker::with('pegawai')->findOrFail($id);

    $tracker->update([
        'dikonfirmasi_at'   => now(),
        'dikonfirmasi_oleh' => auth()->id(),
        'status_saat_ini'   => 'Proses', // Update status agar langsung berubah di UI
    ]);

    ActivityLogger::logAdminAction(
        "Mengkonfirmasi {$tracker->kategori} untuk pegawai {$tracker->pegawai->nama} (NIP: {$tracker->nip})"
    );

    return response()->json([
        'success' => true,
        'message' => 'Tugas berhasil dikonfirmasi!',
    ]);
}

/**
 * Sinkronisasi Data Manual (E-HRM -> Seeder -> Tracker)
 */
public function syncData()
{
    try {
        // Dispatch job ke background queue
        \App\Jobs\ProcessSyncData::dispatch();

        ActivityLogger::logAdminAction("Memulai Sinkronisasi Data Manual (Berjalan di Latar Belakang)");

        // Inisialisasi Cache Progress
        // Step 1: Data Utama Pegawai
        // Step 2: Riwayat Jabatan
        // Step 3: Angka Kredit
        // Step 4: Riwayat Diklat
        Cache::put('sync_status', [
            'progress' => 0,
            'current_step' => 1,
            'step_1_status' => 'pending', 
            'step_2_status' => 'pending',
            'step_3_status' => 'pending',
            'step_4_status' => 'pending',
            'detail_text' => 'Bersiap memulai sinkronisasi...'
        ], now()->addMinutes(15)); // Expire in 15 mins just in case

        // --- TAMBAHAN FIX ---
        // Biar user nggak perlu ngetik "php artisan queue:work" di terminal terpisah
        $artisan = base_path('artisan');
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B php {$artisan} queue:work --once > NUL 2>&1", "r"));
        } else {
            exec("php {$artisan} queue:work --once > /dev/null 2>&1 &");
        }
        // --------------------

        return response()->json([
            'success' => true,
            'message' => 'Proses sinkronisasi telah masuk antrean latar belakang. Silakan cek beberapa saat lagi.',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal Memulai Sinkronisasi: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Mengambil status sinkronisasi terbaru dari Cache.
 */
public function syncProgress()
{
    // Ambil default null atau state 'pending' jika cache nggak ada.
    $status = Cache::get('sync_status', [
        'progress' => 0,
        'current_step' => 1,
        'step_1_status' => 'pending', 
        'step_2_status' => 'pending',
        'step_3_status' => 'pending',
        'step_4_status' => 'pending',
        'detail_text' => 'Bersiap memulai sinkronisasi...'
    ]);

    return response()->json($status);
}
}
