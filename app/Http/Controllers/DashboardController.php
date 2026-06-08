<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\LampiranCetakSurat;
use App\Models\NotifikasiRules; // Added
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Mail\ManualNotification;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. DATA KARTU STATISTIK (Ringkasan)
        $totalPegawai = Pegawai::count();
        
        $tenggatMendesak = DashboardTracker::whereNull('dikonfirmasi_at')
                                       ->where(function($q) {
                                           $q->whereIn('status_saat_ini', ['Usulan', 'Upload E-HRM', 'Menunggu SKP', 'Proses Pengaktifan']);
                                       })
                                       ->count();
                                           
        // REVISI: 'Usulan' sekarang status warning (merah).
        // Yang sedang "Di-usulkan/Diproses" (kuning) adalah status 'Proses'.
        $jumlahUsulan = DashboardTracker::where('status_saat_ini', 'Proses')->count();

        // Hitung persentase kepatuhan (Pegawai yang sudah melengkapi dokumen vs Total Pegawai)
        // Karena tracker yang sudah selesai otomatis dihapus, tabel DashboardTracker
        // hanya berisi dokumen yang BELUM diupload atau sedang diproses.
        $pegawaiBelumPatuh = DashboardTracker::whereColumn('dokumen_terupload', '<', 'dokumen_total')
                                             ->distinct('pegawai_id')
                                             ->count('pegawai_id');

        $tingkatKepatuhan = $totalPegawai > 0 
                            ? round((($totalPegawai - $pegawaiBelumPatuh) / $totalPegawai) * 100) 
                            : 0;

        // Tampilkan tracker yang:
        // 1. Belum dikonfirmasi (dikonfirmasi_at = null) — semua kategori
        // 2. Sudah dikonfirmasi tapi masih punya status aktif (Upload E-HRM, Proses, dll) — agar KP tidak hilang setelah ceklis
        // 3. Kategori KGB selalu tampil (multi-step flow)
                // 4. TUBEL tampil hanya saat flow aktif (Sedang Tubel & Proses Pengaktifan Kembali)
        $trackers = DashboardTracker::with('pegawai')
                    ->where(function($query) {
                        $query->whereNull('dikonfirmasi_at')
                              ->orWhere('kategori', 'KGB')
                                                            ->orWhere(function($q) {
                                                                    $q->where('kategori', 'TUBEL')
                                                                        ->whereIn('status_saat_ini', ['Sedang Tubel', 'Proses Pengaktifan Kembali', 'Proses Pengembalian', 'Proses Pengaktifan']);
                                                            })
                              ->orWhereIn('status_saat_ini', ['Upload E-HRM', 'Proses', 'Menunggu UKOM', 'Menunggu SKP']);
                    })
                    ->whereNotIn('status_saat_ini', ['Mendekati', 'Aman', 'Data Tidak Lengkap']) // Hide Mendekati, Aman, and Data Tidak Lengkap
                    ->get();

        $listTubel = $trackers->where('kategori', 'TUBEL')
                              ->sortBy('tanggal_target');

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
        
        // Pisahkan berdasarkan kategori tracker, dengan fallback ke tipe_jabatan jika kategori tidak spesifik
        $kpStruktural = $listKenaikanPangkat->filter(function($item) {
            if ($item->kategori === 'KP_Struktural') return true;
            if (in_array($item->kategori, ['KP_Jafung', 'KP_Reguler'])) return false;

            $tipe = strtolower(trim($item->pegawai->tipe_jabatan ?? ''));
            return in_array($tipe, ['struktural', 'jabatan struktural']) || (!empty($item->pegawai->kd_eselon) && empty($tipe));
        });

        $kpFungsional = $listKenaikanPangkat->filter(function($item) {
            if ($item->kategori === 'KP_Jafung') return true;
            if (in_array($item->kategori, ['KP_Struktural', 'KP_Reguler'])) return false;

            $tipe = strtolower(trim($item->pegawai->tipe_jabatan ?? ''));
            return in_array($tipe, ['fungsional', 'jafung', 'jabatan fungsional']) || (!empty($item->pegawai->jenjang) && empty($tipe));
        });

        $kpReguler = $listKenaikanPangkat->filter(function($item) {
            if ($item->kategori === 'KP_Reguler') return true;
            if (in_array($item->kategori, ['KP_Struktural', 'KP_Jafung'])) return false;

            $tipe = strtolower(trim($item->pegawai->tipe_jabatan ?? ''));
            return in_array($tipe, ['reguler', 'pelaksana', 'jabatan pelaksana']) || (empty($tipe) && empty($item->pegawai->kd_eselon) && empty($item->pegawai->jenjang));
        })->sortBy('tanggal_target');

        // Monitoring Kompetensi (Diklat)
        $listMonitoringDiklat = $trackers->where('kategori', 'DIKLAT_BELUM_UPLOAD')->sortBy('tanggal_target');

        // Ambil template manual untuk modal reminder di dashboard
        $templates = NotifikasiRules::where('interval_hari', 0)->get();

        // [FITUR BARU] Ambil waktu sinkronisasi terakhir dari Log Aktivitas
        $lastSyncLog = \App\Models\Logs::where('tipe', 'SYSTEM_LOG')
            ->where('deskripsi', 'LIKE', '%Background Sync Selesai%')
            ->latest('waktu')
            ->first();

        if ($lastSyncLog) {
            $syncDate = \Carbon\Carbon::parse($lastSyncLog->waktu);
            $lastSyncTime = $syncDate->translatedFormat('d M Y, H:i') . ' WIB';
            $isDataFresh = $syncDate->diffInHours(now()) < 24;
        } else {
            $lastSyncTime = 'Belum pernah';
            $isDataFresh = false;
        }

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
            'listTubel',
            'listMonitoringDiklat',
            'templates',
            'lastSyncTime',
            'isDataFresh'
        ));
    }

    /**
     * Detail Diklat bermasalah per pegawai (AJAX)
     */
    public function diklatDetail(string $nip, string $kategori)
    {
        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();
        $today = \Carbon\Carbon::now();
        $diklat = \App\Models\RiwayatDiklat::where('nip', $nip)->get();

        if ($kategori === 'DIKLAT_BELUM_UPLOAD') {
            $filtered = $diklat->filter(function ($d) use ($today) {
                return ($d->status_diklat == 0 && $d->tanggal_selesai && $today->greaterThan(\Carbon\Carbon::parse($d->tanggal_selesai)))
                    || ($d->status_diklat == 1 && (empty($d->arsip) || empty($d->nomor_sertifikat) || $d->nomor_sertifikat === '-'));
            });
        } else {
            $filtered = collect(); // Fallback jika kategori selain diklat dilempar ke sini
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
                'arsip_biasa' => $d->file_sertifikat ? 'Ada' : 'Tidak Ada',
                'arsip_bpsdm' => $d->arsip ? 'Ada' : 'Tidak Ada',
                'status' => $d->status_diklat == 1 ? 'Lulus' : 'Proses',
            ])->values(),
        ]);
    }

/**
 * Pindahkan Tracker ke Uji Kompetensi (UKOM)
 */
public function moveToUkom(Request $request, string $id)
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
public function confirmTracker(Request $request, string $id)
{
    $tracker = DashboardTracker::with('pegawai')->findOrFail($id);

    $isTubel = $tracker->kategori === 'TUBEL';
    $newStatus = $isTubel ? 'Selesai' : 'Upload E-HRM';

    $tracker->update([
        'dikonfirmasi_at'   => now(),
        'dikonfirmasi_oleh' => Auth::id(),
        'status_saat_ini'   => $newStatus,
    ]);

    if ($isTubel) {
        ActivityLogger::logAdminAction(
            "Mengkonfirmasi pengaktifan kembali selesai untuk TUBEL pegawai {$tracker->pegawai->nama} (NIP: {$tracker->nip})"
        );
        return response()->json([
            'success' => true,
            'message' => 'Pengaktifan kembali Tubel berhasil diselesaikan!',
        ]);
    } else {
        ActivityLogger::logAdminAction(
            "Mengkonfirmasi TTE selesai untuk {$tracker->kategori} pegawai {$tracker->pegawai->nama} (NIP: {$tracker->nip})"
        );
        return response()->json([
            'success' => true,
            'message' => 'TTE berhasil dikonfirmasi! Status diperbarui ke Upload E-HRM.',
        ]);
    }
}

/**
 * Handle kelulusan UKOM
 */
public function setKelulusanUkom(Request $request, string $id)
{
    $tracker = DashboardTracker::with('pegawai')->findOrFail($id);
    $isLulus = $request->input('lulus');

    if ($isLulus) {
        $tracker->update([
            'kategori'          => 'KJ_Jafung',
            'status_saat_ini'   => 'Usulan',
            'keterangan'        => 'Lulus UKOM, dilanjutkan ke pengejuan Kenaikan Jenjang'
        ]);

        ActivityLogger::logAdminAction(
            "Mengesahkan Kelulusan UKOM untuk {$tracker->pegawai->nama} (NIP: {$tracker->pegawai->nip}) dan mengirim ke antrean KJ_Jafung"
        );

        return response()->json([
            'success' => true,
            'message' => 'Pegawai Lulus UKOM dan masuk antrean (Usulan) Kenaikan Jenjang!',
        ]);
    } else {
        $tracker->update([
            'keterangan'        => 'Tidak Lulus UKOM'
        ]);

        ActivityLogger::logAdminAction(
            "Mengatur Tidak Lulus UKOM untuk {$tracker->pegawai->nama} (NIP: {$tracker->pegawai->nip})"
        );

        return response()->json([
            'success' => true,
            'message' => 'Pegawai ditandai Tidak Lulus UKOM',
        ]);
    }
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

    public function cetakSuratKj(Request $request, string $id)
    {
        $tracker = DashboardTracker::with('pegawai')->findOrFail($id);
        $pegawai = $tracker->pegawai;

        if (!$pegawai) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        // Siapkan data untuk template
        $data = [
            'nomor_surat' => $request->query('nomor_surat') ?: '............................................',
            'tanggal_surat' => $request->query('tanggal') ? \Carbon\Carbon::parse($request->query('tanggal'))->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y'),
            'nama_pegawai' => $pegawai->nama . ($pegawai->gelar_belakang ? ', ' . $pegawai->gelar_belakang : ''),
            'jabatan_fungsional' => $pegawai->jabatan_fungsional ?? $pegawai->jabatan,
            'jenjang_baru' => $pegawai->jenjang_baru ?? 'Ahli Madya', // Diambil dari field yang sesuai (bisa disesuaikan)
            'ref_nota_dinas' => 'KP0303/B/Sp/' . date('Y') . '/506', // Nomor ref Nota Biro Kepegawaian
            'tgl_nota_dinas' => '31 Maret 2026', // Placeholder
            'nomor_surat_bkn' => '1589/B-BJ.03.02/SD/C/' . date('Y'), // Placeholder
            'tgl_surat_bkn' => '25 Maret 2026', // Placeholder
            'narahubung_nama' => 'Julia',
            'narahubung_hp' => '0822-9824-6907',
            'narahubung_email' => 'julia.pujilestari@pu.go.id',
        ];

        $pdf = Pdf::loadView('surat.surat_usul_kj_pdf', ['data' => $data])
                  ->setPaper('A4', 'portrait');

        $filename = 'Surat_Usul_KJ_' . str_replace(' ', '_', $pegawai->nama) . '_' . date('Ymd') . '.pdf';
        // Log Aktivitas
        if (!$request->has('preview')) {
            ActivityLogger::logSystem('Mencetak Surat Usul Kenaikan Jenjang untuk: ' . $pegawai->nama, Auth::user()->name);
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Generate PDF Bundle KJ: Cover Nota Dinas + Semua Lampiran
     */
    public function generateBundleKj(Request $request, string $id)
    {
        $tracker = DashboardTracker::with(['pegawai', 'lampiran_cetak_surat'])->findOrFail($id);
        $pegawai = $tracker->pegawai;

        if (!$pegawai) {
            return response()->json(['success' => false, 'message' => 'Data pegawai tidak ditemukan.'], 404);
        }

        // Ambil lampiran yang punya file fisik, urutkan
        $lampirans = $tracker->lampiran_cetak_surat
            ->whereNotNull('file_path')
            ->sortBy('urutan');

        // Siapkan data surat cover
        $data = [
            'nomor_surat'        => $request->query('nomor_surat', '............................................'),
            'tanggal_surat'      => $request->query('tanggal')
                ? \Carbon\Carbon::parse($request->query('tanggal'))->translatedFormat('d F Y')
                : \Carbon\Carbon::now()->translatedFormat('d F Y'),
            'tujuan_surat'       => $request->query('tujuan_surat', 'Kepala Biro Kepegawaian, Organisasi dan Tata Laksana'),
            'nama_ttd'           => $request->query('nama_ttd', 'Komang Sri Hartini'),
            'nip_ttd'            => $request->query('nip_ttd', '196811201994032001'),
            'jabatan_ttd'        => $request->query('jabatan_ttd', 'Kepala Pusat Data dan Teknologi Informasi'),
            'nama_pegawai'       => $pegawai->nama . ($pegawai->gelar_belakang ? ', ' . $pegawai->gelar_belakang : ''),
            'jabatan_fungsional' => $pegawai->jabatan_fungsional ?? $pegawai->jabatan,
            'jenjang_baru'       => $pegawai->jenjang_baru ?? 'Ahli Madya',
            'ref_nota_dinas'     => 'KP0303/B/Sp/' . date('Y') . '/506',
            'tgl_nota_dinas'     => '31 Maret ' . date('Y'),
            'nomor_surat_bkn'    => '1589/B-BJ.03.02/SD/C/' . date('Y'),
            'tgl_surat_bkn'      => '25 Maret ' . date('Y'),
            'narahubung_nama'    => $request->query('narahubung_nama', 'Sdri. Julia'),
            'narahubung_hp'      => $request->query('narahubung_hp', '0822-9824-6907'),
            'narahubung_email'   => $request->query('narahubung_email', 'julia.pujilestari@pu.go.id'),
        ];

        // Pastikan folder temp ada
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

        // Generate cover surat (Nota Dinas) jadi PDF dulu
        $coverPdf     = Pdf::loadView('surat.surat_usul_kj_pdf', ['data' => $data])->setPaper('A4', 'portrait');
        $coverPath    = $tempDir . '/cover_kj_' . time() . '_' . uniqid() . '.pdf';
        file_put_contents($coverPath, $coverPdf->output());

        // Jika ada lampiran, gabungkan
        if ($lampirans->count() > 0) {
            $suratService = new \App\Services\SuratPengajuanService();
            $bundlePath   = $suratService->appendLampiran(
                $coverPath, 
                $lampirans,
                $data['nomor_surat'],
                $data['tanggal_surat']
            );
            @unlink($coverPath); // Hapus file cover sementara
            $finalPath = $bundlePath;
        } else {
            $finalPath = $coverPath;
        }

        $filename = 'Surat_Usul_KJ_Bundle_' . str_replace(' ', '_', $pegawai->nama) . '_' . date('Ymd') . '.pdf';

        if (!$request->has('preview')) {
            ActivityLogger::logSystem(
                'Mencetak Bundle Surat Usul KJ untuk: ' . $pegawai->nama . ' (dengan ' . $lampirans->count() . ' lampiran)',
                Auth::user()->name
            );

            // Bersihkan fisik lampiran dari server setelah di-bundle jadi PDF
            foreach ($lampirans as $lamp) {
                if (\Storage::disk('public')->exists($lamp->file_path)) {
                    \Storage::disk('public')->delete($lamp->file_path);
                }
                $lamp->delete();
            }

            return response()->download($finalPath, $filename)->deleteFileAfterSend(true);
        }

        return response()->file($finalPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);
    }
}
