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
use App\Mail\ManualNotification;
use Illuminate\Support\Facades\Mail;

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
        $listKenaikanPangkat = $trackers->whereIn('kategori', ['KP_Jafung', 'KP_Struktural', 'KP_Reguler']);
        $listKenaikanJenjang = $trackers->where('kategori', 'KJ_Jafung');
        $listUkom            = $trackers->where('kategori', 'UKOM');
        $ukomMadya = $listUkom->filter(function($item) {
            return str_contains(strtolower($item->pegawai->tipe_jabatan ?? ''), 'fungsional') && 
                   str_contains(strtolower($item->pegawai->jenjang ?? ''), 'ahli muda');
        });
        $ukomBiasa = $listUkom->reject(function($item) use ($ukomMadya) {
            return $ukomMadya->contains('id', $item->id);
        });
        
        $listKGB             = $trackers->where('kategori', 'KGB')->sortBy(function($item) {
            if ($item->status_saat_ini === 'Usulan') return 1;
            if ($item->status_saat_ini === 'Upload E-HRM') return 2;
            if ($item->status_saat_ini === 'Proses') return 3;
            return 4;
        });
        
        // Pisahkan berdasarkan tipe_jabatan
        $kpStruktural = $listKenaikanPangkat->filter(function($item) {
            return str_contains(strtolower($item->pegawai->tipe_jabatan), 'struktural');
        });

        $kpFungsional = $listKenaikanPangkat->filter(function($item) {
            return str_contains(strtolower($item->pegawai->tipe_jabatan), 'fungsional');
        });

        $kpReguler = $trackers->where('kategori', 'KP_Reguler')->sortBy('tanggal_target');

        // Monitoring Kompetensi (Diklat)
        $diklatHutang = $trackers->where('kategori', 'DIKLAT_HUTANG')->sortBy('tanggal_target');
        $diklatAnomali = $trackers->where('kategori', 'DIKLAT_ANOMALI');
        $listMonitoringDiklat = $trackers->whereIn('kategori', ['DIKLAT_HUTANG', 'DIKLAT_ANOMALI']);

        // Ambil template manual untuk modal reminder di dashboard
        $templates = NotifikasiRules::where('interval_hari', 0)->get();

        return view('dashboard.index', compact(
            'totalPegawai', 
            'tenggatMendesak', 
            'jumlahUsulan', 
            'tingkatKepatuhan',
            'kpStruktural',
            'kpFungsional',
            'kpReguler',
            'listKenaikanPangkat',
            'listKenaikanJenjang',
            'listUkom',
            'ukomBiasa',
            'ukomMadya',
            'listKGB',
            'diklatHutang',
            'diklatAnomali',
            'listMonitoringDiklat',
            'templates'
        ));
    }

    /**
     * Detail Diklat bermasalah per pegawai (AJAX)
     */
    public function diklatDetail($nip, $kategori)
    {
        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();
        $today = \Carbon\Carbon::now();
        $diklat = \App\Models\RiwayatDiklat::where('nip', $nip)->get();

        if ($kategori === 'DIKLAT_HUTANG') {
            $filtered = $diklat->filter(function ($d) use ($today) {
                return $d->status_diklat == 0
                    && $d->tanggal_selesai
                    && $today->greaterThan(\Carbon\Carbon::parse($d->tanggal_selesai));
            });
        } else {
            $filtered = $diklat->filter(function ($d) {
                return $d->status_diklat == 1
                    && (empty($d->arsip) || empty($d->nomor_sertifikat) || $d->nomor_sertifikat === '-');
            });
        }

        return response()->json([
            'pegawai' => $pegawai->nama,
            'nip' => $nip,
            'kategori' => $kategori,
            'total' => $filtered->count(),
            'data' => $filtered->map(fn($d) => [
                'nama_diklat' => $d->nama_diklat,
                'tanggal_mulai' => $d->tanggal_mulai ? \Carbon\Carbon::parse($d->tanggal_mulai)->format('d M Y') : '-',
                'tanggal_selesai' => $d->tanggal_selesai ? \Carbon\Carbon::parse($d->tanggal_selesai)->format('d M Y') : '-',
                'jenis' => $d->jenis_diklat ?? '-',
                'sertifikat' => $d->nomor_sertifikat ?: '-',
                'arsip' => $d->arsip ? 'Ada' : 'Tidak Ada',
                'status' => $d->status_diklat == 1 ? 'Lulus' : 'Proses',
            ])->values(),
        ]);
    }

/**
 * Pindahkan Tracker ke Uji Kompetensi (UKOM)
 */
public function moveToUkom(Request $request, $id)
{
    $tracker = DashboardTracker::with('pegawai')->findOrFail($id);

    $tracker->update([
        'kategori'          => 'UKOM',
        'status_saat_ini'   => 'Usulan',
        'keterangan'        => 'Pegawai masuk kategori Uji Kompetensi'
    ]);

    // Kirim Notifikasi Email
    if ($tracker->pegawai->email) {
        $subject = 'Pemberitahuan Uji Kompetensi (UKOM)';
        $message = "Anda telah masuk ke dalam kategori Uji Kompetensi (UKOM) untuk proses kenaikan jenjang Anda. Mohon segera persiapkan diri dan lengkapi dokumen yang diperlukan.\n\nTerima kasih.";
        
        try {
            Mail::to($tracker->pegawai->email)->send(new ManualNotification($tracker->pegawai, $subject, $message));
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim notifikasi UKOM ke {$tracker->pegawai->email}: " . $e->getMessage());
        }
    }

    ActivityLogger::logAdminAction(
        "Memindahkan {$tracker->pegawai->nama} (NIP: {$tracker->pegawai->nip}) ke kategori Uji Kompetensi (UKOM)"
    );

    return response()->json([
        'success' => true,
        'message' => 'Pegawai berhasil didaftarkan Uji Kompetensi dan notifikasi telah dikirim!',
    ]);
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
        'status_saat_ini'   => 'Upload E-HRM', // Proses TTE → Upload E-HRM
    ]);

    ActivityLogger::logAdminAction(
        "Mengkonfirmasi TTE selesai untuk {$tracker->kategori} pegawai {$tracker->pegawai->nama} (NIP: {$tracker->nip})"
    );

    return response()->json([
        'success' => true,
        'message' => 'TTE berhasil dikonfirmasi! Status diperbarui ke Upload E-HRM.',
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
            pclose(popen("start /B php {$artisan} queue:work --once --timeout=3600 > NUL 2>&1", "r"));
        } else {
            exec("php {$artisan} queue:work --once --timeout=3600 > /dev/null 2>&1 &");
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
