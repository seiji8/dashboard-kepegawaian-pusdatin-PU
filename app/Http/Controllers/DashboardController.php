<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\KelengkapanDokumen;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;

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
        $listKGB             = $trackers->where('kategori', 'KGB')->sortBy(function($item) {
            switch ($item->status_saat_ini) {
                case 'Usulan': return 1;
                case 'Upload E-HRM': return 2;
                case 'Proses': return 3;
                default: return 4;
            }
        });
        
        // Pisahkan Struktural vs Fungsional (Asumsi ada logic pembeda, sementara kita filter manual)
        // Disini saya contohkan filter sederhana
        $kpStruktural = $listKenaikanPangkat->filter(function($item) {
            return str_contains($item->pegawai->jabatan_saat_ini, 'Kepala') || str_contains($item->pegawai->jabatan_saat_ini, 'Kabid');
        });

        $kpFungsional = $listKenaikanPangkat->filter(function($item) {
            return !str_contains($item->pegawai->jabatan_saat_ini, 'Kepala');
        });

        return view('dashboard.index', compact(
            'totalPegawai', 
            'tenggatMendesak', 
            'jumlahUsulan', 
            'tingkatKepatuhan',
            'kpStruktural',
            'kpFungsional',
            'listKGB'
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
}