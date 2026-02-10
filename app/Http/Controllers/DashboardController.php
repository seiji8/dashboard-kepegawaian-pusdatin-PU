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
                                           $q->where('status_saat_ini', 'Mendekati')
                                             ->orWhere('status_saat_ini', 'Menunggu SKP');
                                       })
                                       ->count();
                                           
        $jumlahUsulan = DashboardTracker::where('status_saat_ini', 'Usulan')->count();

        // Hitung persentase kepatuhan (Dokumen Terupload / Total Dokumen)
        $totalDokumenWajib = DashboardTracker::sum('dokumen_total');
        $totalDokumenAda   = DashboardTracker::sum('dokumen_terupload');
        
        $tingkatKepatuhan = $totalDokumenWajib > 0 
                            ? round(($totalDokumenAda / $totalDokumenWajib) * 100) 
                            : 0;

        // 2. DATA TUGAS (Task List)
        // Kita ambil data tracker dan load relasi pegawainya
        $trackers = DashboardTracker::with('pegawai')->whereNull('dikonfirmasi_at')->get();

        // Kelompokkan berdasarkan Kategori untuk Accordion
        $listKenaikanPangkat = $trackers->where('kategori', 'KP_Jafung'); // Gabung Struktural & Jafung logic nanti
        $listKGB             = $trackers->where('kategori', 'KGB');
        
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