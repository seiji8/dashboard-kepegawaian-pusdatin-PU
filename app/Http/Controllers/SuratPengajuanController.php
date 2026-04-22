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
            'kgb_sk_pejabat' => $request->sk_lama_pejabat ?? 'Kepala Biro Kepegawaian, Organisasi dan Tata Laksana',
            'kgb_sk_nomor'   => $request->sk_lama_nomor ?? '318/KPTS/M/2026',
            'kgb_sk_tanggal' => $request->sk_lama_tanggal ?? '20 Februari 2026',
            'kgb_gaji_lama_angka'     => $request->gaji_lama ? number_format($request->gaji_lama, 0, ',', '.') : '',
            'kgb_gaji_lama_terbilang' => $request->gaji_lama ? ucwords($this->terbilang($request->gaji_lama)) . ' Rupiah' : '',
            'kgb_gaji_baru_angka'     => $request->gaji_baru ? number_format($request->gaji_baru, 0, ',', '.') : '',
            'kgb_gaji_baru_terbilang' => $request->gaji_baru ? ucwords($this->terbilang($request->gaji_baru)) . ' Rupiah' : '',
            'pegawai_list'   => $trackers->map(function ($t) use ($masaKerjaInput, $kategori) {
                // Hitung masa kerja: prioritas input manual > auto dari tmt_cpns
                $masaKerja = $masaKerjaInput[$t->id] ?? '';
                $kgbMasaKerjaLama = '';
                $kgbMasaKerjaBaru = '';

                if ($t->pegawai && $t->pegawai->tmt_cpns) {
                    $tmtCpns = Carbon::parse($t->pegawai->tmt_cpns);
                    $now = Carbon::now();
                    
                    // Masa Kerja standard (sampai hari ini)
                    if (empty($masaKerja)) {
                        $years = $tmtCpns->diffInYears($now);
                        $months = $tmtCpns->copy()->addYears($years)->diffInMonths($now);
                        $masaKerja = sprintf('%02d Th / %02d Bln', $years, $months);
                    }

                    // Khusus KGB: Hitung Masa Kerja Golongan (Lama) dan Masa Kerja (Baru)
                    if ($kategori === 'KGB') {
                        // Lama: dari CPNS sampai tmt_kgb_terakhir
                        if ($t->pegawai->tmt_kgb_terakhir) {
                            $tmtKgbLama = Carbon::parse($t->pegawai->tmt_kgb_terakhir);
                            $yearsLama = $tmtCpns->diffInYears($tmtKgbLama);
                            $monthsLama = $tmtCpns->copy()->addYears($yearsLama)->diffInMonths($tmtKgbLama);
                            $kgbMasaKerjaLama = sprintf('%02d tahun %02d bulan', $yearsLama, $monthsLama);
                        }
                        
                        // Baru: dari CPNS sampai tanggal target KGB (atau tanggal surat jika kosong)
                        $tmtKgbBaru = $t->tanggal_target ? Carbon::parse($t->tanggal_target) : $now;
                        $yearsBaru = $tmtCpns->diffInYears($tmtKgbBaru);
                        $monthsBaru = $tmtCpns->copy()->addYears($yearsBaru)->diffInMonths($tmtKgbBaru);
                        $kgbMasaKerjaBaru = sprintf('%02d tahun %02d bulan', $yearsBaru, $monthsBaru);
                    }
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
                    'kgb_masa_kerja_lama' => $kgbMasaKerjaLama,
                    'kgb_masa_kerja_baru' => $kgbMasaKerjaBaru,
                    'tracker_id'       => $t->id,
                ];
            })->toArray(),
            'total_pegawai' => $trackers->count(),
        ];

        // Generate PDF
        $filename = 'Surat_Pengajuan_' . str_replace(' ', '_', $data['kategori_label']) . '_' . date('Ymd_His') . '.pdf';

        if (in_array($data['kategori'], ['KP', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler'])) {
            
            // Generate Hal 1 (Portrait)
            $pdf1 = Pdf::loadView('surat.surat_pengajuan_kp_hal1', ['data' => $data]);
            $pdf1->setPaper('A4', 'portrait');
            
            // Generate Hal 2 (Landscape)
            $pdf2 = Pdf::loadView('surat.surat_pengajuan_kp_hal2', ['data' => $data]);
            $pdf2->setPaper('A4', 'landscape');
            
            // Folder sementara untuk menampung PDF
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $file1 = $tempDir . '/hal1_' . time() . '_' . uniqid() . '.pdf';
            $file2 = $tempDir . '/hal2_' . time() . '_' . uniqid() . '.pdf';
            
            file_put_contents($file1, $pdf1->output());
            file_put_contents($file2, $pdf2->output());
            
            // Merge menggunakan FPDI
            $fpdi = new \setasign\Fpdi\Fpdi();
            $filesToMerge = [$file1, $file2];
            
            foreach ($filesToMerge as $file) {
                $pageCount = $fpdi->setSourceFile($file);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $fpdi->importPage($pageNo);
                    $size = $fpdi->getTemplateSize($templateId);
                    
                    // Deteksi orientasi otomatis dari dokumen aslinya
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    
                    $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($templateId);
                }
            }
            
            $mergedFile = $tempDir . '/' . $filename;
            $fpdi->Output('F', $mergedFile);
            
            // Bersihkan sampah halaman awal
            @unlink($file1);
            @unlink($file2);
            
            // Download hasil merge dan hapus saat selesai dikirim
            return response()->download($mergedFile)->deleteFileAfterSend(true);
            
        } elseif ($data['kategori'] === 'KGB') {
            $pdf = Pdf::loadView('surat.surat_pengajuan_kgb_pdf', ['data' => $data]);
            $pdf->setPaper('A4', 'portrait');
        } else {
            $pdf = Pdf::loadView('surat.surat_pengajuan_pdf', ['data' => $data]);
            $pdf->setPaper('A4', 'portrait');
        }

        return $pdf->download($filename);
    }

    /**
     * Helper Fungsi Terbilang
     */
    private function terbilang($x) {
        return trim($this->_terbilang($x));
    }

    private function _terbilang($x) {
        $x = abs($x);
        $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $temp = "";

        if ($x < 12) {
            $temp = " " . $angka[$x];
        } else if ($x < 20) {
            $temp = $this->_terbilang($x - 10) . " belas";
        } else if ($x < 100) {
            $temp = $this->_terbilang(floor($x / 10)) . " puluh" . $this->_terbilang($x % 10);
        } else if ($x < 200) {
            $temp = " seratus" . $this->_terbilang($x - 100);
        } else if ($x < 1000) {
            $temp = $this->_terbilang(floor($x / 100)) . " ratus" . $this->_terbilang($x % 100);
        } else if ($x < 2000) {
            $temp = " seribu" . $this->_terbilang($x - 1000);
        } else if ($x < 1000000) {
            $temp = $this->_terbilang(floor($x / 1000)) . " ribu" . $this->_terbilang($x % 1000);
        } else if ($x < 1000000000) {
            $temp = $this->_terbilang(floor($x / 1000000)) . " juta" . $this->_terbilang($x % 1000000);
        }

        return $temp;
    }
}
