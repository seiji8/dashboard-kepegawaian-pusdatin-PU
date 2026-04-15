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
            $trackers = DashboardTracker::with('pegawai')
                ->where('kategori', $kategori)
                ->whereNull('dikonfirmasi_at')
                ->whereIn('status_saat_ini', ['Usulan', 'Mendekati', 'Proses'])
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

        // AUTO UPDATE STATUS: Usulan → Proses (trigger oleh cetak surat)
        // Yang sudah Proses = cetak ulang, status TIDAK berubah
        $updatedCount = 0;
        foreach ($trackers as $tracker) {
            if ($tracker->status_saat_ini === 'Usulan') {
                $tracker->update([
                    'status_saat_ini' => 'Proses',
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

        // Masa kerja dari modal form (key = tracker_id, value = string masa kerja)
        $masaKerjaInput = $request->masa_kerja ?? [];

        // Siapkan data untuk template
        $data = [
            'kategori'       => $kategori,
            'kategori_label' => $this->kategoriLabels[$kategori] ?? str_replace('_', ' ', $kategori),
            'nomor_surat'    => $request->nomor_surat ?? '......../........./........',
            'tanggal_surat'  => $request->tanggal_surat 
                ? Carbon::parse($request->tanggal_surat)->isoFormat('D MMMM Y')
                : Carbon::now()->isoFormat('D MMMM Y'),
            'tujuan_surat'   => $request->tujuan_surat ?? "Kepala Biro Kepegawaian, Organisasi, dan Tata\nLaksana, Sekretariat Jenderal, Kementerian\nPekerjaan Umum",
            'nama_ttd'       => $request->nama_ttd ?? 'Komang Sri Hartini',
            'nip_ttd'        => $request->nip_ttd ?? '........................',
            'jabatan_ttd'    => $request->jabatan_ttd ?? 'Kepala Pusat Data dan Teknologi Informasi',
            'kppn'           => $request->kppn ?? '',
            'pegawai_list'   => $trackers->map(function ($t) use ($masaKerjaInput) {
                // Hitung masa kerja: prioritas input manual > auto dari tmt_cpns
                $masaKerja = $masaKerjaInput[$t->id] ?? '';
                if (empty($masaKerja) && $t->pegawai && $t->pegawai->tmt_cpns) {
                    $tmtCpns = Carbon::parse($t->pegawai->tmt_cpns);
                    $now = Carbon::now();
                    $years = $tmtCpns->diffInYears($now);
                    $months = $tmtCpns->copy()->addYears($years)->diffInMonths($now);
                    $masaKerja = sprintf('%02d Th / %02d Bln', $years, $months);
                }

                return [
                    'nama'             => $t->pegawai->nama ?? '-',
                    'nip'              => $t->pegawai->nip ?? '-',
                    'pangkat_golongan' => $t->pegawai->pangkat_golongan ?? '-',
                    'jabatan'          => $t->pegawai->jabatan_saat_ini ?? $t->pegawai->tipe_jabatan ?? '-',
                    'jenjang'          => $t->pegawai->jenjang ?? '-',
                    'tmt_target'       => $t->tanggal_target 
                        ? Carbon::parse($t->tanggal_target)->format('d-m-Y')
                        : '-',
                    'keterangan'       => $t->keterangan ?? '-',
                    'kategori'         => $t->kategori,
                    'masa_kerja'       => $masaKerja,
                    'tracker_id'       => $t->id,
                ];
            })->toArray(),
            'total_pegawai' => $trackers->count(),
        ];

        // Generate PDF
        if (in_array($data['kategori'], ['KP', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler'])) {
            $pdf = Pdf::loadView('surat.surat_pengajuan_kp_pdf', ['data' => $data]);
            $pdf->setPaper('A4', 'portrait');
        } elseif ($data['kategori'] === 'KGB') {
            $pdf = Pdf::loadView('surat.surat_pengajuan_kgb_pdf', ['data' => $data]);
            $pdf->setPaper('A4', 'portrait');
        } else {
            $pdf = Pdf::loadView('surat.surat_pengajuan_pdf', ['data' => $data]);
            $pdf->setPaper('A4', 'portrait');
        }

        $filename = 'Surat_Pengajuan_' . str_replace(' ', '_', $data['kategori_label']) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
