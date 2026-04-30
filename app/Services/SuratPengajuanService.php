<?php

namespace App\Services;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class SuratPengajuanService
{
    /**
     * Generate PDF Surat Pengajuan
     *
     * @param array $requestData Input dari user
     * @param Collection $trackers Data tracker dari database
     * @param array $kategoriLabels Mapping label kategori
     * @return string Path absolut ke file PDF hasil generate (sudah di merge/single)
     */
    public function generateSurat(array $requestData, Collection $trackers, array $kategoriLabels): string
    {
        $kategori = $requestData['kategori'];
        $masaKerjaInput = $requestData['masa_kerja'] ?? [];

        // Guard: KP & KGB tidak dicetak dari dashboard — suratnya dibuat langsung di E-HRM
        $kategoriDilarang = ['KGB', 'KP', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler'];
        if (in_array($kategori, $kategoriDilarang)) {
            throw new \InvalidArgumentException('Surat KP dan KGB dicetak langsung dari E-HRM, bukan dari dashboard ini.');
        }

        // Siapkan data untuk template
        $data = [
            'kategori'       => $kategori,
            'kategori_label' => $kategoriLabels[$kategori] ?? str_replace('_', ' ', $kategori),
            'nomor_surat'    => $requestData['nomor_surat'] ?? '......../........./........',
            'tanggal_surat'  => !empty($requestData['tanggal_surat']) 
                ? Carbon::parse($requestData['tanggal_surat'])->isoFormat('D MMMM Y')
                : Carbon::now()->isoFormat('D MMMM Y'),
            'tujuan_surat'   => $requestData['tujuan_surat'] ?? "Kepala Biro Kepegawaian, Organisasi, dan Tata\nLaksana, Sekretariat Jenderal, Kementerian\nPekerjaan Umum",
            'nama_ttd'       => $requestData['nama_ttd'] ?? 'Komang Sri Hartini',
            'nip_ttd'        => $requestData['nip_ttd'] ?? '........................',
            'jabatan_ttd'    => $requestData['jabatan_ttd'] ?? 'Kepala Pusat Data dan Teknologi Informasi',
            'kppn'           => $requestData['kppn'] ?? '',
            'kgb_sk_pejabat' => $requestData['sk_lama_pejabat'] ?? 'Kepala Biro Kepegawaian, Organisasi dan Tata Laksana',
            'kgb_sk_nomor'   => $requestData['sk_lama_nomor'] ?? '318/KPTS/M/2026',
            'kgb_sk_tanggal' => $requestData['sk_lama_tanggal'] ?? '20 Februari 2026',
            'kgb_gaji_lama_angka'     => !empty($requestData['gaji_lama']) ? number_format($requestData['gaji_lama'], 0, ',', '.') : '',
            'kgb_gaji_lama_terbilang' => !empty($requestData['gaji_lama']) ? ucwords($this->terbilang($requestData['gaji_lama'])) . ' Rupiah' : '',
            'kgb_gaji_baru_angka'     => !empty($requestData['gaji_baru']) ? number_format($requestData['gaji_baru'], 0, ',', '.') : '',
            'kgb_gaji_baru_terbilang' => !empty($requestData['gaji_baru']) ? ucwords($this->terbilang($requestData['gaji_baru'])) . ' Rupiah' : '',
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

        // Pastikan folder temp ada
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = 'Surat_Pengajuan_' . str_replace(' ', '_', $data['kategori_label']) . '_' . date('Ymd_His') . '.pdf';
        $finalPath = $tempDir . '/' . $filename;

        // Generate PDF based on Category
        if (in_array($data['kategori'], ['KP', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler'])) {
            // KP menggunakan FPDI Merge (Hal 1 Portrait, Hal 2 Landscape)
            $pdf1 = Pdf::loadView('surat.surat_pengajuan_kp_hal1', ['data' => $data])->setPaper('A4', 'portrait');
            $pdf2 = Pdf::loadView('surat.surat_pengajuan_kp_hal2', ['data' => $data])->setPaper('A4', 'landscape');
            
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
                    
                    // Deteksi orientasi otomatis
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    
                    $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($templateId);
                }
            }
            
            $fpdi->Output('F', $finalPath);
            
            // Clean up split files
            @unlink($file1);
            @unlink($file2);
            
        } elseif ($data['kategori'] === 'KGB') {
            // KGB (Single Portrait)
            $pdf = Pdf::loadView('surat.surat_pengajuan_kgb_pdf', ['data' => $data])->setPaper('A4', 'portrait');
            file_put_contents($finalPath, $pdf->output());
        } else {
            // General (Single Portrait)
            $pdf = Pdf::loadView('surat.surat_pengajuan_pdf', ['data' => $data])->setPaper('A4', 'portrait');
            file_put_contents($finalPath, $pdf->output());
        }

        return $finalPath;
    }

    /**
     * Helper Fungsi Terbilang
     */
    public function terbilang($x) {
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
