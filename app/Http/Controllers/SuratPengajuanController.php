<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DashboardTracker;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratPengajuanController extends Controller
{
    /**
     * Mapping kategori ke label yang human-readable
     */
    private $kategoriLabels = [
        'KGB'           => 'Kenaikan Gaji Berkala',
        'KP'            => 'Kenaikan Pangkat',
        'KP_Jafung'     => 'Kenaikan Pangkat Fungsional',
        'KP_Struktural' => 'Kenaikan Pangkat Struktural',
        'KP_Reguler'    => 'Kenaikan Pangkat Reguler',
        'KJ_Jafung'     => 'Kenaikan Jenjang Fungsional',
        'UKOM'          => 'Uji Kompetensi',
        'TUBEL'         => 'Pengaktifan Kembali Tubel',
    ];

    /**
     * Preview: Ambil data pegawai per kategori, grouped by periode (tanggal_target).
     * Dipanggil via AJAX untuk populate modal.
     */
    public function preview($kategori)
    {
        // Validasi kategori
        $validKategori = array_keys($this->kategoriLabels);
        
        // KP (Kenaikan Pangkat) bisa berisi gabungan sub-kategori
        if ($kategori === 'KP') {
            $trackers = DashboardTracker::with('pegawai')
                ->whereIn('kategori', ['KP_Jafung', 'KP_Struktural', 'KP_Reguler'])
                ->whereNull('dikonfirmasi_at')
                ->whereIn('status_saat_ini', ['Usulan', 'Mendekati', 'Proses'])
                ->orderBy('tanggal_target')
                ->get();
        } elseif (in_array($kategori, $validKategori)) {
            $allowedStatuses = ['Usulan', 'Mendekati', 'Proses'];
            if ($kategori === 'TUBEL') {
                $allowedStatuses = ['Sedang Tubel', 'Proses Pengaktifan Kembali', 'Proses Pengaktifan'];
            }

            $trackers = DashboardTracker::with('pegawai')
                ->where('kategori', $kategori)
                ->whereNull('dikonfirmasi_at')
                ->whereIn('status_saat_ini', $allowedStatuses)
                ->orderBy('tanggal_target')
                ->get();
        } else {
            return response()->json(['success' => false, 'message' => 'Kategori tidak valid'], 400);
        }

        // Grouping by periode (bulan-tahun dari tanggal_target)
        $grouped = [];
        foreach ($trackers as $tracker) {
            if (!$tracker->pegawai) continue;

            $periode = $tracker->tanggal_target 
                ? Carbon::parse($tracker->tanggal_target)->format('Y-m')
                : 'tanpa-periode';

            $periodeLabel = $tracker->tanggal_target 
                ? Carbon::parse($tracker->tanggal_target)->isoFormat('MMMM Y')
                : 'Tanpa Periode';

            if (!isset($grouped[$periode])) {
                $grouped[$periode] = [
                    'periode_key'   => $periode,
                    'periode_label' => $periodeLabel,
                    'pegawai'       => [],
                ];
            }

            $grouped[$periode]['pegawai'][] = [
                'tracker_id'       => $tracker->id,
                'nama'             => $tracker->pegawai->nama,
                'nip'              => $tracker->pegawai->nip,
                'pangkat_golongan' => $tracker->pegawai->pangkat_golongan ?? '-',
                'jabatan'          => $tracker->pegawai->jabatan_saat_ini ?? $tracker->pegawai->tipe_jabatan ?? '-',
                'jenjang'          => $tracker->pegawai->jenjang ?? '-',
                'tmt_target'       => $tracker->tanggal_target 
                    ? Carbon::parse($tracker->tanggal_target)->format('d-m-Y') 
                    : '-',
                'kategori'         => $tracker->kategori,
                'status'           => $tracker->status_saat_ini,
                'keterangan'       => $tracker->keterangan ?? '-',
            ];
        }

        return response()->json([
            'success'        => true,
            'kategori'       => $kategori,
            'kategori_label' => $this->kategoriLabels[$kategori] ?? str_replace('_', ' ', $kategori),
            'total'          => $trackers->count(),
            'groups'         => array_values($grouped),
        ]);
    }

    /**
     * Konfirmasi Usulan KP & KGB (tanpa cetak surat — surat dibuat di E-HRM)
     * Flow: Usulan → Proses TTE langsung
     */
    public function konfirmasiUsulan(Request $request)
    {
        $request->validate([
            'kategori'    => 'required|string',
            'tracker_ids' => 'required|array|min:1',
            'tracker_ids.*' => 'integer|exists:dashboard_tracker,id',
            'catatan'     => 'nullable|string|max:500',
        ]);

        // Hanya izinkan KP, KGB & TUBEL
        $allowedKategori = ['KGB', 'KP', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'TUBEL'];
        if (!in_array($request->kategori, $allowedKategori)) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak valid untuk konfirmasi usulan.'], 400);
        }

        $trackers = DashboardTracker::with('pegawai')
            ->whereIn('id', $request->tracker_ids)
            ->get();

        if ($trackers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $updatedCount = 0;
        foreach ($trackers as $tracker) {
            if (in_array($tracker->status_saat_ini, ['Usulan', 'Mendekati', 'Proses Pengaktifan'])) {
                $tracker->update([
                    'status_saat_ini' => 'Proses',
                    'keterangan'      => $request->catatan ?? $tracker->keterangan,
                    // Untuk Tubel, jangan set dikonfirmasi_at dulu sampai tahap "Selesai"
                    'dikonfirmasi_at' => $tracker->kategori === 'TUBEL' ? null : now(),
                ]);
                $updatedCount++;
            }
        }

        // Log aktivitas
        if (function_exists('logActivity')) {
            $pegawaiNames = $trackers->pluck('pegawai.nama')->filter()->implode(', ');
            $label = $this->kategoriLabels[$request->kategori] ?? $request->kategori;
            $catatan = $request->catatan ? " | Catatan: {$request->catatan}" : '';
            logActivity(
                'Konfirmasi Usulan',
                "Konfirmasi usulan {$label} untuk {$trackers->count()} pegawai ({$updatedCount} status → Proses TTE): {$pegawaiNames}{$catatan}"
            );
        }

        return response()->json([
            'success' => true,
            'message' => "{$updatedCount} pegawai berhasil dikonfirmasi dan masuk ke Proses TTE.",
            'updated' => $updatedCount,
        ]);
    }

    /**
     * Generate PDF Surat Pengajuan
     */
    public function generate(Request $request)
    {
         $request->validate([
            'kategori'      => 'required|string',
            'tracker_ids'   => 'required|array|min:1',
            'tracker_ids.*' => 'integer|exists:dashboard_tracker,id',
            'nomor_surat'   => 'nullable|string|max:100',
            'tanggal_surat' => 'nullable|date',
            'tujuan_surat'  => 'nullable|string|max:255',
            'nama_ttd'      => 'nullable|string|max:150',
            'nip_ttd'       => 'nullable|string|max:30',
            'jabatan_ttd'   => 'nullable|string|max:150',
            'kppn'          => 'nullable|string|max:100',
            'masa_kerja'    => 'nullable|array',
            'sk_lama_pejabat' => 'nullable|string',
            'sk_lama_nomor'   => 'nullable|string',
            'sk_lama_tanggal' => 'nullable|string',
            'gaji_lama'       => 'nullable|numeric',
            'gaji_baru'       => 'nullable|numeric',
        ]);

        $kategori   = $request->kategori;
        $trackerIds = $request->tracker_ids;

        // Ambil data tracker yang dipilih
        $trackers = DashboardTracker::with('pegawai')
            ->whereIn('id', $trackerIds)
            ->orderBy('tanggal_target')
            ->get();

        if ($trackers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // AUTO UPDATE STATUS by kategori (trigger oleh cetak surat)
        // - Umum: Usulan -> Proses
        // - TUBEL: Sedang Tubel -> Proses Pengaktifan Kembali
        // Yang sudah di status proses = cetak ulang, status tidak berubah
        $updatedCount = 0;
        foreach ($trackers as $tracker) {
            $newStatus = null;

            if ($tracker->kategori === 'TUBEL' && $tracker->status_saat_ini === 'Sedang Tubel') {
                $newStatus = 'Proses Pengaktifan Kembali';
            } elseif ($tracker->status_saat_ini === 'Usulan') {
                $newStatus = 'Proses';
            }

            if ($newStatus !== null) {
                $tracker->update([
                    'status_saat_ini' => $newStatus,
                ]);
                $updatedCount++;
            }
        }

        // Log aktivitas cetak surat
        if (function_exists('logActivity')) {
            $pegawaiNames = $trackers->pluck('pegawai.nama')->filter()->implode(', ');
            $label = $this->kategoriLabels[$kategori] ?? $kategori;
            logActivity(
                'Cetak Surat Pengajuan',
                "Mencetak surat {$label} untuk {$trackers->count()} pegawai ({$updatedCount} status diperbarui ke Proses TTE): {$pegawaiNames}"
            );
        }

        // Gunakan SuratPengajuanService untuk logic PDF yang rumit
        $pdfService = app(\App\Services\SuratPengajuanService::class);
        $finalPdfPath = $pdfService->generateSurat($request->all(), $trackers, $this->kategoriLabels);
        
        $filename = basename($finalPdfPath);
        return response()->download($finalPdfPath, $filename)->deleteFileAfterSend(true);
    }
}
