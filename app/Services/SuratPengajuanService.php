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
     * Append halaman lampiran ke PDF yang sudah ada.
     * Logika pintar: 2 gambar dengan judul SAMA → jejeran 1 halaman.
     *
     * @param string $basePdfPath Path PDF surat pengantar yang sudah di-generate
     * @param \Illuminate\Support\Collection $lampirans Collection KelengkapanDokumen
     * @return string Path PDF bundle final
     */
    public function appendLampiran(
        string $basePdfPath, 
        $lampirans, 
        string $nomorSurat = '............................................', 
        string $tanggalSurat = '............................................',
        ?string $judulLampiranPertama = null
    ): string {
        // Kelompokkan lampiran berdasarkan judul_lampiran
        $groups = $lampirans->groupBy('judul_lampiran');

        // Buka PDF base (surat pengantar) menggunakan FPDI
        $fpdi = new \setasign\Fpdi\Fpdi('P', 'mm', 'A4');
        $fpdi->SetAutoPageBreak(false);

        // Import halaman-halaman dari surat pengantar (base)
        $pageCount = $fpdi->setSourceFile($basePdfPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tpl);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl);
        }

        // Variabel penanda halaman lampiran pertama
        $isFirstLampiranPage = true;

        // Loop setiap grup lampiran
        foreach ($groups as $judul => $items) {
            $images = $items->filter(fn($i) => in_array($i->mime_type, ['image/jpeg', 'image/png', 'image/jpg']));
            $pdfs   = $items->filter(fn($i) => $i->mime_type === 'application/pdf');

            // Kumpulkan semua halaman/media secara berurutan
            $pages = [];
            foreach ($pdfs as $pdfItem) {
                $pdfPath = storage_path('app/public/' . $pdfItem->file_path);
                if (!file_exists($pdfPath)) continue;

                $pdfPageCount = $fpdi->setSourceFile($pdfPath);
                for ($p = 1; $p <= $pdfPageCount; $p++) {
                    $pages[] = [
                        'type'    => 'pdf_page',
                        'path'    => $pdfPath,
                        'page_no' => $p,
                    ];
                }
            }

            foreach ($images as $imgItem) {
                $imgPath = storage_path('app/public/' . $imgItem->file_path);
                if (!file_exists($imgPath)) continue;

                $pages[] = [
                    'type' => 'image',
                    'path' => $imgPath,
                ];
            }

            // Percabangan untuk Mode Grid 3-dalam-1
            if (count($pages) === 3) {
                $this->drawGrid3In1($fpdi, $pages, $judul, $nomorSurat, $tanggalSurat, $isFirstLampiranPage, $judulLampiranPertama);
                $isFirstLampiranPage = false;
                continue;
            }

            // Proses file PDF: tiap halaman jadi halaman baru
            foreach ($pdfs as $pdfItem) {
                $pdfPath = storage_path('app/public/' . $pdfItem->file_path);
                if (!file_exists($pdfPath)) continue;

                $pdfPageCount = $fpdi->setSourceFile($pdfPath);
                for ($p = 1; $p <= $pdfPageCount; $p++) {
                    $fpdi->AddPage('P', 'A4');
                    
                    if ($isFirstLampiranPage) {
                        $actualJudul = !empty($judulLampiranPertama) ? $judulLampiranPertama : $judul;
                        $this->drawLampiranReference($fpdi, $nomorSurat, $tanggalSurat);
                        $boxY = $this->drawLampiranHeader($fpdi, $actualJudul, 45);
                        $isFirstLampiranPage = false;
                    } else {
                        $boxY = $this->drawLampiranHeader($fpdi, $judul, 15);
                    }
                    $boxH = 285 - $boxY;

                    $tpl  = $fpdi->importPage($p);
                    $size = $fpdi->getTemplateSize($tpl);

                    // Hitung skala agar muat di dalam kotak dengan ruang napas 85% khusus PDF
                    $boxX = 25; $boxW = 166;
                    $marginRatio = 1.0;
                    $ratio = min(($boxW * $marginRatio) / $size['width'], ($boxH * $marginRatio) / $size['height']);
                    $newW  = $size['width'] * $ratio;
                    $newH  = $size['height'] * $ratio;
                    $xPos  = $boxX + ($boxW - $newW) / 2;
                    $yPos  = $boxY + 5; // Beri sela 5mm dari judul di atasnya agar lebih lega

                    $fpdi->useTemplate($tpl, $xPos, $yPos, $newW, $newH);

                    // Gambar border hitam tipis di sekeliling halaman PDF hasil skala
                    $fpdi->SetDrawColor(0, 0, 0);
                    $fpdi->SetLineWidth(0.2);
                    $fpdi->Rect($xPos, $yPos, $newW, $newH);
                }
            }

            // Proses gambar
            if ($images->count() === 2) {
                // MODE JEJERAN: 2 gambar dengan judul sama → 1 halaman sebelahan
                $fpdi->AddPage('P', 'A4');
                
                if ($isFirstLampiranPage) {
                    $actualJudul = !empty($judulLampiranPertama) ? $judulLampiranPertama : $judul;
                    $this->drawLampiranReference($fpdi, $nomorSurat, $tanggalSurat);
                    $boxY = $this->drawLampiranHeader($fpdi, $actualJudul, 45);
                    $isFirstLampiranPage = false;
                } else {
                    $boxY = $this->drawLampiranHeader($fpdi, $judul, 15);
                }
                $boxH = 285 - $boxY;

                $totalW = 166; $gap = 5;
                $halfW = ($totalW - $gap) / 2;

                $imgList = $images->values();
                foreach ($imgList as $idx => $imgItem) {
                    $imgPath = storage_path('app/public/' . $imgItem->file_path);
                    if (!file_exists($imgPath)) continue;

                    $xPos = 25 + ($idx * ($halfW + $gap));

                    [$origW, $origH] = getimagesize($imgPath);
                    $pxToMm = 25.4 / 96;
                    $physicalW = $origW * $pxToMm;
                    $physicalH = $origH * $pxToMm;
                    
                    $fitRatio = min($halfW / $physicalW, $boxH / $physicalH);
                    $finalRatio = $fitRatio < 1 ? $fitRatio : 1;
                    
                    $newW  = $physicalW * $finalRatio;
                    $newH  = $physicalH * $finalRatio;
                    $imgX  = $xPos + ($halfW - $newW) / 2;
                    $imgY  = $boxY; // Rata atas

                    $fpdi->Image($imgPath, $imgX, $imgY, $newW, $newH);
                    $fpdi->Rect($imgX, $imgY, $newW, $newH);
                }

            } elseif ($images->count() >= 1) {
                // MODE NORMAL: 1 gambar = 1 halaman penuh
                foreach ($images as $imgItem) {
                    $imgPath = storage_path('app/public/' . $imgItem->file_path);
                    if (!file_exists($imgPath)) continue;

                    $fpdi->AddPage('P', 'A4');
                    
                    if ($isFirstLampiranPage) {
                        $actualJudul = !empty($judulLampiranPertama) ? $judulLampiranPertama : $judul;
                        $this->drawLampiranReference($fpdi, $nomorSurat, $tanggalSurat);
                        $boxY = $this->drawLampiranHeader($fpdi, $actualJudul, 45);
                        $isFirstLampiranPage = false;
                    } else {
                        $boxY = $this->drawLampiranHeader($fpdi, $judul, 15);
                    }
                    $boxH = 285 - $boxY;

                    $boxX = 25; $boxW = 166;

                    [$origW, $origH] = getimagesize($imgPath);
                    $pxToMm = 25.4 / 96;
                    $physicalW = $origW * $pxToMm;
                    $physicalH = $origH * $pxToMm;
                    
                    $fitRatio = min($boxW / $physicalW, $boxH / $physicalH);
                    $finalRatio = $fitRatio < 1 ? $fitRatio : 1;
                    
                    $newW  = $physicalW * $finalRatio;
                    $newH  = $physicalH * $finalRatio;
                    $imgX  = $boxX + ($boxW - $newW) / 2;
                    $imgY  = $boxY; // Rata atas

                    $fpdi->Image($imgPath, $imgX, $imgY, $newW, $newH);
                    $fpdi->Rect($imgX, $imgY, $newW, $newH);
                }
            }
        }

        // Simpan PDF bundle final
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

        $bundlePath = $tempDir . '/bundle_' . time() . '_' . uniqid() . '.pdf';
        $fpdi->Output('F', $bundlePath);

        return $bundlePath;
    }

    /**
     * Gambar header halaman lampiran: judul saja, tanpa bold dan tanpa garis.
     */
    private function drawLampiranHeader(\setasign\Fpdi\Fpdi $fpdi, string $judul, int $startY = 15): float
    {
        // Judul lampiran (regular, bukan bold)
        $fpdi->SetFont('Arial', '', 11);
        $fpdi->SetXY(25, $startY);
        $fpdi->MultiCell(166, 5, $judul, 0, 'L');

        return $fpdi->GetY() + 8; // Kembalikan posisi Y akhir + 8mm padding (jarak aman dari konten di bawahnya)
    }

    /**
     * Gambar blok referensi di pojok kanan atas halaman lampiran pertama.
     */
    private function drawLampiranReference(\setasign\Fpdi\Fpdi $fpdi, string $nomorSurat, string $tanggalSurat): void
    {
        // Blok teks di kanan atas (X=111, Y=15, lebar=80mm agar sejajar dengan margin kanan 19mm)
        $fpdi->SetFont('Arial', '', 10);
        $fpdi->SetXY(111, 15);
        $fpdi->MultiCell(80, 5, 'Lampiran Nota Dinas Kepala Pusat Data dan Teknologi Informasi', 0, 'L');

        $afterTitleY = $fpdi->GetY();
        $fpdi->SetXY(111, $afterTitleY + 1);
        $fpdi->Cell(20, 5, 'Nomor', 0, 0, 'L');
        $fpdi->Cell(5,  5, ':', 0, 0, 'L');
        $fpdi->Cell(55, 5, $nomorSurat, 0, 1, 'L');

        $fpdi->SetX(111);
        $fpdi->Cell(20, 5, 'Tanggal', 0, 0, 'L');
        $fpdi->Cell(5,  5, ':', 0, 0, 'L');
        $fpdi->Cell(55, 5, $tanggalSurat, 0, 1, 'L');
    }

    /**
     * Menggambar 3 halaman/media dalam 1 kertas A4 Portrait (Mode Grid 3-dalam-1)
     */
    private function drawGrid3In1(
        \setasign\Fpdi\Fpdi $fpdi, 
        array $pages, 
        string $judul, 
        string $nomorSurat, 
        string $tanggalSurat, 
        bool $isFirstPage,
        ?string $judulLampiranPertama = null
    ): void {
        $fpdi->AddPage('P', 'A4');

        if ($isFirstPage) {
            $actualJudul = !empty($judulLampiranPertama) ? $judulLampiranPertama : $judul;
            $this->drawLampiranReference($fpdi, $nomorSurat, $tanggalSurat);
            $boxY = $this->drawLampiranHeader($fpdi, $actualJudul, 45);
        } else {
            $boxY = $this->drawLampiranHeader($fpdi, $judul, 15);
        }
        $boxH = 285 - $boxY;

        // Bagi lebar atas menjadi 2 kolom (dengan sela 5mm)
        $halfW = (166 - 5) / 2;
        $halfH = ($boxH - 5) / 2; // Tinggi batas maksimum teoretis untuk baris atas

        // 1. Kalkulasikan dimensi hasil skala terlebih dahulu untuk 3 media agar posisi Y baris kedua bisa diatur dinamis
        $scaledPlausible = [];
        foreach ($pages as $idx => $page) {
            if ($idx >= 3) break;
            
            $wTarget = ($idx < 2) ? $halfW : 166;
            $hTarget = $halfH;

            $origW = 0;
            $origH = 0;

            if ($page['type'] === 'pdf_page') {
                $fpdi->setSourceFile($page['path']);
                $tpl = $fpdi->importPage($page['page_no']);
                $size = $fpdi->getTemplateSize($tpl);
                $origW = $size['width'];
                $origH = $size['height'];
            } elseif ($page['type'] === 'image') {
                [$origW, $origH] = getimagesize($page['path']);
                $pxToMm = 25.4 / 96;
                $origW = $origW * $pxToMm;
                $origH = $origH * $pxToMm;
            }

            if ($origW > 0 && $origH > 0) {
                $ratio = min($wTarget / $origW, $hTarget / $origH);
                $newW = $origW * $ratio;
                $newH = $origH * $ratio;
            } else {
                $newW = $wTarget;
                $newH = $hTarget;
            }

            $scaledPlausible[$idx] = [
                'w' => $newW,
                'h' => $newH,
                'page' => $page
            ];
        }

        // 2. Hitung tinggi riil baris atas & tentukan Y baris bawah secara dinamis agar rapat dan rapi
        $h1 = $scaledPlausible[0]['h'] ?? 0;
        $h2 = $scaledPlausible[1]['h'] ?? 0;
        $maxTopH = max($h1, $h2);

        $rowGap = 8; // Sela vertikal antara baris atas dan bawah
        $yTop = $boxY;
        $yBottom = $boxY + $maxTopH + $rowGap;
        $remainingH = 285 - $yBottom;

        // Skalakan ulang media ke-3 agar optimal memanfaatkan sisa tinggi halaman sesungguhnya
        if (isset($scaledPlausible[2])) {
            $page3 = $scaledPlausible[2]['page'];
            $origW = 0;
            $origH = 0;

            if ($page3['type'] === 'pdf_page') {
                $fpdi->setSourceFile($page3['path']);
                $tpl = $fpdi->importPage($page3['page_no']);
                $size = $fpdi->getTemplateSize($tpl);
                $origW = $size['width'];
                $origH = $size['height'];
            } elseif ($page3['type'] === 'image') {
                [$origW, $origH] = getimagesize($page3['path']);
                $pxToMm = 25.4 / 96;
                $origW = $origW * $pxToMm;
                $origH = $origH * $pxToMm;
            }

            if ($origW > 0 && $origH > 0) {
                $ratio = min(166 / $origW, $remainingH / $origH);
                $newW = $origW * $ratio;
                $newH = $origH * $ratio;
            } else {
                $newW = 166;
                $newH = $remainingH;
            }

            $scaledPlausible[2]['w'] = $newW;
            $scaledPlausible[2]['h'] = $newH;
        }

        // 3. Gambar masing-masing media ke halaman PDF dengan rata atas (top alignment)
        foreach ($scaledPlausible as $idx => $item) {
            $page = $item['page'];
            $newW = $item['w'];
            $newH = $item['h'];

            if ($idx === 0) {
                $xPos = 25 + ($halfW - $newW) / 2;
                $yPos = $yTop;
            } elseif ($idx === 1) {
                $xPos = (25 + $halfW + 5) + ($halfW - $newW) / 2;
                $yPos = $yTop;
            } else {
                $xPos = 25 + (166 - $newW) / 2;
                $yPos = $yBottom;
            }

            if ($page['type'] === 'pdf_page') {
                $fpdi->setSourceFile($page['path']);
                $tpl = $fpdi->importPage($page['page_no']);
                $fpdi->useTemplate($tpl, $xPos, $yPos, $newW, $newH);
                
                // Gambar border hitam tipis di sekeliling media hasil skala
                $fpdi->SetDrawColor(0, 0, 0);
                $fpdi->SetLineWidth(0.2);
                $fpdi->Rect($xPos, $yPos, $newW, $newH);

            } elseif ($page['type'] === 'image') {
                $fpdi->Image($page['path'], $xPos, $yPos, $newW, $newH);

                // Gambar border hitam tipis di sekeliling media hasil skala
                $fpdi->SetDrawColor(0, 0, 0);
                $fpdi->SetLineWidth(0.2);
                $fpdi->Rect($xPos, $yPos, $newW, $newH);
            }
        }
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
