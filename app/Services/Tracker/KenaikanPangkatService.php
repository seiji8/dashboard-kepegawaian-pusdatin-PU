<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use App\Helpers\ActivityLogger;

class KenaikanPangkatService implements TrackerInterface
{
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        $matriksKamus = $context['matriksKamus'] ?? collect();

        $this->processJafung($pegawai, $today, $daftarUsulanBaru, $matriksKamus);
        $this->processStruktural($pegawai, $today, $daftarUsulanBaru);
        $this->processReguler($pegawai, $today, $daftarUsulanBaru);
    }

    private function processJafung(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, \Illuminate\Support\Collection $matriksKamus): void
    {
        // Skip dummy/test data as they are manually seeded and don't have real Angka Kredit history
        if (str_contains(strtolower($pegawai->id_pegawai_api ?? ''), 'dummy') || 
            str_contains(strtolower($pegawai->nip ?? ''), 'dummy')) {
            return;
        }

        $tipeJabatan = strtolower(trim($pegawai->tipe_jabatan ?? ''));
        $isFungsional = in_array($tipeJabatan, ['fungsional', 'jafung', 'jabatan fungsional']) || 
                        (!empty($pegawai->jenjang) && empty($tipeJabatan));

        if ($isFungsional && !empty($pegawai->pangkat_golongan) && !empty($pegawai->jabatan_saat_ini)) {
            $normalizedJenjang = ucwords(strtolower(trim($pegawai->jenjang)));
            
            $matriks = $matriksKamus->first(function ($item) use ($normalizedJenjang, $pegawai) {
                return strtolower($item->jabatan_asal) === strtolower($normalizedJenjang) && 
                       strtolower($item->pangkat_asal) === strtolower(trim($pegawai->pangkat_golongan));
            });

            if ($matriks && !$matriks->is_naik_jenjang) {
                $targetAK = 0;
                if ($matriks->target_ak > 0) {
                    $targetAK = $matriksKamus->where('jabatan_asal', $matriks->jabatan_asal)
                                             ->where('id', '<=', $matriks->id)
                                             ->sum('target_ak');
                }
                                         
                $koefisienTahunan = $matriks->koefisien_tahunan ?? 0;
                
                $kategoriSekarang = 'KP_Jafung';
                $kategoriLawan = 'KJ_Jafung';
                $dokumenTotal = 2;
                
                $namaProses = 'Kenaikan Pangkat';
                $tujuanProses = $matriks->next_pangkat ?? 'Pangkat Berikutnya';

                $latestAK = $pegawai->riwayatAngkaKredit->first();
                $currentAK = $this->calculateCurrentAK($pegawai, $latestAK);

                if ($targetAK > 0) {
                    $kekuranganAK = $targetAK - $currentAK;
                    $akTriwulanBaik = ($koefisienTahunan / 4) * 1.0;
                    $akTriwulanSangatBaik = ($koefisienTahunan / 4) * 1.5;

                    $statusAK = '';
                    $keteranganAK = '';
                    $kurangFormat = number_format($kekuranganAK, 3, ',', '.'); 

                    $existingAK = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                        ->whereIn('kategori', [$kategoriSekarang, 'UKOM'])
                        ->first();

                    $currentAKStatus = $existingAK ? $existingAK->status_saat_ini : null;
                    $isConfirmedAK = $existingAK && $existingAK->dikonfirmasi_at;

                    $skipTrackerUpdate = false;

                    if ($currentAKStatus === 'Upload E-HRM' || $isConfirmedAK) {
                        $statusAK = 'Upload E-HRM';
                        $keteranganAK = 'TTE Selesai. Menunggu upload E-HRM.';
                    } elseif ($currentAKStatus === 'Proses') {
                        $statusAK = 'Proses';
                        $keteranganAK = 'Sedang diproses admin';
                    } elseif (is_null($latestAK) || $currentAK == 0) {
                        $statusAK = 'Data Tidak Lengkap'; 
                        $keteranganAK = 'Peringatan: Data Riwayat AK tidak ditemukan di e-HRM atau bernilai 0. Segera upload/update SK PAK Anda.';
                    } else {
                        if ($kekuranganAK <= 0) {
                            $skpResult = $this->checkSkpCompliance($pegawai);

                            if ($skpResult['meet']) {
                                $statusAK = 'Usulan';
                                $keteranganAK = "AK & SKP memenuhi target. Segera usulkan {$namaProses} ke {$tujuanProses}";
                            } else {
                                $statusAK = 'Aman';
                                $keteranganAK = "AK memenuhi target, namun " . $skpResult['reason'];
                            }
                        } elseif ($kekuranganAK <= $akTriwulanBaik) {
                            $statusAK = 'Mendekati';
                            $keteranganAK = "Kurang {$kurangFormat} AK. Dapat dicapai dalam 1 Triwulan ke depan dengan predikat minimal BAIK";
                        } elseif ($kekuranganAK <= $akTriwulanSangatBaik) {
                            $statusAK = 'Mendekati';
                            $keteranganAK = "Kurang {$kurangFormat} AK. Dapat dicapai dalam 1 Triwulan ke depan jika mendapat predikat SANGAT BAIK";
                        } else {
                            $statusAK = 'Aman';
                            $keteranganAK = "Masih kurang {$kurangFormat} AK. Belum bisa dicapai dalam 1 Triwulan ke depan";
                        }
                    }

                    if ($statusAK === 'Mendekati') {
                        $this->sendMendekatiNotification($pegawai, $kategoriSekarang, $keteranganAK);
                        $this->sendSkpTriwulanReminder($pegawai);
                        // JANGAN return di sini agar tracker tetap terupdate dengan status Mendekati
                    }
                    if ($statusAK === 'Aman') {
                        $this->sendSkpTriwulanReminder($pegawai);
                    }

                    $targetDate = null;
                    if (in_array($statusAK, ['Usulan', 'Proses'])) {
                        if ($existingAK && $existingAK->tanggal_target) {
                            $targetDate = $existingAK->tanggal_target;
                        } else {
                            $targetDate = Carbon::now()->format('Y-m-d');
                        }
                    }

                    if (!$skipTrackerUpdate) {
                        $this->updateTracker(
                            $pegawai,
                            $statusAK,
                            $keteranganAK,
                            $kategoriSekarang,
                            $dokumenTotal,
                            $targetDate,
                            $daftarUsulanBaru,
                            $existingAK,
                            $kategoriLawan
                        );
                    }
                }
            }
        }
    }

    private function calculateCurrentAK(Pegawai $pegawai, ?\App\Models\RiwayatAngkaKredit $latestAK): float
    {
        $currentAK = 0;

        if ($latestAK && $pegawai->tmt_pangkat_terakhir) {
            $tmtAK = Carbon::parse($latestAK->tmt_angka_kredit);
            $tmtPangkat = Carbon::parse($pegawai->tmt_pangkat_terakhir);
            
            if ($tmtAK->greaterThan($tmtPangkat)) {
                $currentAK = $latestAK->total_kredit;
            }
        } elseif ($latestAK && empty($pegawai->tmt_pangkat_terakhir)) {
            $currentAK = $latestAK->total_kredit;
        }

        return (float) $currentAK;
    }

    private function checkSkpCompliance(Pegawai $pegawai): array
    {
        $annualSkps = \App\Models\RiwayatSkp::where('nip', $pegawai->nip)
            ->where('status', 'LIKE', '%Tahunan%')
            ->orderBy('tahun', 'desc')
            ->limit(2)
            ->get();
        
        $skpMemenuhi = true;
        $badSkpYear = null;
        $alasanSkp = "";

        if ($annualSkps->count() < 2) {
            $skpMemenuhi = false;
            $alasanSkp = "Data SKP Tahunan 2 tahun terakhir tidak lengkap di E-HRM";
        } else {
            foreach ($annualSkps as $skp) {
                $nilai = strtoupper(trim($skp->nilai_skp));
                if (!in_array($nilai, ['BAIK', 'SANGAT BAIK'])) {
                    $skpMemenuhi = false;
                    if (is_null($badSkpYear) || $skp->tahun > $badSkpYear) {
                        $badSkpYear = $skp->tahun;
                    }
                }
            }
            if (!$skpMemenuhi) {
                $targetYear = $badSkpYear + 2;
                $alasanSkp = "Nilai SKP Tahunan ({$badSkpYear}) bukan BAIK/SANGAT BAIK. Harus menunggu 2 tahun (Target Usulan: Tahun {$targetYear})";
            }
        }

        return [
            'meet' => $skpMemenuhi,
            'reason' => $alasanSkp
        ];
    }

    private function sendMendekatiNotification(Pegawai $pegawai, string $kategoriSekarang, string $keteranganAK): void
    {
        $kategoriLawan = 'KJ_Jafung';
        $namaProses = 'Kenaikan Pangkat';

        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
            ->whereIn('kategori', [$kategoriSekarang, $kategoriLawan])
            ->where('status_saat_ini', 'Mendekati')
            ->delete();

        $notifCacheKey = 'mendekati_notif_' . $pegawai->id_pegawai_api . '_' . $kategoriSekarang;
        if (!Cache::has($notifCacheKey)) {
            $notifiable = User::where('email', $pegawai->email)->first();
            if (!$notifiable && $pegawai->email) {
                $notifiable = Notification::route('mail', $pegawai->email);
            }
            if ($notifiable) {
                $subjekMendekati = "🔔 Informasi Angka Kredit: Mendekati Target {$namaProses}";
                $pesanMendekati = "Yth. {$pegawai->nama} (NIP: {$pegawai->nip}),\n\n"
                    . "Angka Kredit (AK) Anda saat ini telah mendekati target untuk {$namaProses}.\n"
                    . "{$keteranganAK}\n\n"
                    . "Harap terus tingkatkan kinerja Anda agar target AK dapat segera tercapai.\n\n"
                    . "Terima kasih.";
                try {
                    $notifiable->notify(new SystemAlertNotification($pegawai, $subjekMendekati, $pesanMendekati));
                    Cache::put($notifCacheKey, true, now()->addDays(30));
                    ActivityLogger::logSystem("Mengirim notifikasi 'Mendekati' AK ke pegawai {$pegawai->nama} ({$kategoriSekarang})", $pegawai->nip);
                } catch (\Exception $e) {}
            }
        }
    }

    private function updateTracker(
        Pegawai $pegawai,
        string $statusAK,
        string $keteranganAK,
        string $kategoriSekarang,
        int $dokumenTotal,
        ?string $targetDate,
        array &$daftarUsulanBaru,
        ?DashboardTracker $existingAK,
        string $kategoriLawan
    ): void {
        $dokumenTerupload = 0;
        if (!empty($pegawai->arsip_skp_2_tahun) && count($pegawai->arsip_skp_2_tahun) >= 2) {
            $dokumenTerupload++;
        }
        if (!empty($pegawai->sk_pangkat_terakhir)) {
            $dokumenTerupload++;
        } elseif ($existingAK) {
            $uploadedNames = $existingAK->kelengkapan_dokumen->where('is_uploaded', true)->pluck('nama_dokumen')->toArray();
            if (in_array("SK Pangkat Terakhir", $uploadedNames)) {
                $dokumenTerupload++;
            }
        }

        $tracker = DashboardTracker::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id_pegawai_api,
                'kategori'   => $kategoriSekarang,
            ],
            [
                'status_saat_ini'   => $statusAK,
                'keterangan'        => $keteranganAK,
                'dokumen_total'     => $dokumenTotal,
                'dokumen_terupload' => $dokumenTerupload,
                'tanggal_target'    => $targetDate,
            ]
        );

        if ($statusAK == 'Usulan' && !$tracker->notified_at) {
            $daftarUsulanBaru[] = [
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'kategori' => $kategoriSekarang
            ];
            $tracker->update(['notified_at' => now()]);
        }

        if ($statusAK == 'Upload E-HRM') {
            $docs = [];
            if ($kategoriSekarang === 'KP_Struktural') {
                $docs = ['SK Pangkat Terakhir', 'SK Jabatan Terakhir'];
            } elseif ($kategoriSekarang === 'KP_Reguler' || $kategoriSekarang === 'KP_Jafung') {
                $docs = ['SK Pangkat Terakhir'];
            }
            if (!empty($docs)) {
                $this->sendUploadEhrmNotification($pegawai, $kategoriSekarang, $tracker, $docs);
            }
        }

        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
            ->where('kategori', $kategoriLawan)
            ->delete();
    }

    private function processStruktural(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru): void
    {
        if (!empty($pegawai->pangkat_golongan) && !empty($pegawai->kd_eselon)) {
            $eselonMapping = [
                '1' => ['min' => 'IV/d', 'max' => 'IV/e'],
                '2' => ['min' => 'IV/c', 'max' => 'IV/e'],
                '3' => ['min' => 'IV/c', 'max' => 'IV/d'],
                '4' => ['min' => 'IV/b', 'max' => 'IV/c'],
                '5' => ['min' => 'IV/a', 'max' => 'IV/b'],
                '6' => ['min' => 'III/d', 'max' => 'IV/a'],
                '7' => ['min' => 'III/c', 'max' => 'III/d'],
                '8' => ['min' => 'III/b', 'max' => 'III/c'],
                '9' => ['min' => 'III/a', 'max' => 'III/b'],
            ];

            $golruOrder = [
                'I/a', 'I/b', 'I/c', 'I/d',
                'II/a', 'II/b', 'II/c', 'II/d',
                'III/a', 'III/b', 'III/c', 'III/d',
                'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e',
            ];

            $eselon = trim($pegawai->kd_eselon);
            $golru  = trim($pegawai->pangkat_golongan);

            if (isset($eselonMapping[$eselon])) {
                $mapping   = $eselonMapping[$eselon];
                $minGolru  = $mapping['min'];
                $maxGolru  = $mapping['max'];

                $idxGolru = array_search($golru,    $golruOrder);
                $idxMin   = array_search($minGolru, $golruOrder);
                $idxMax   = array_search($maxGolru, $golruOrder);

                $tmtJabatan = $pegawai->tmt_struktural;
                if (!$tmtJabatan && $pegawai->riwayat_jabatan) {
                    $latestJabatan = $pegawai->riwayat_jabatan->first();
                    if ($latestJabatan && $latestJabatan->tmt_jabatan) {
                        $tmtJabatan = $latestJabatan->tmt_jabatan;
                    }
                }

                $tanggalTargetStruktural = null;
                if ($tmtJabatan) {
                    $tanggalTargetStruktural = Carbon::parse($tmtJabatan)->addYear();
                }

                $masaPangkat = 0;
                if ($pegawai->tmt_pangkat_terakhir) {
                    $masaPangkat = Carbon::parse($pegawai->tmt_pangkat_terakhir)->diffInYears($today);
                }

                $statusStruktural    = 'Aman';
                $keteranganStruktural = 'Masih dalam masa aman / Belum memenuhi masa kerja';

                $existingKPStruct = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                    ->where('kategori', 'KP_Struktural')
                    ->first();

                $currentStructStatus = $existingKPStruct ? $existingKPStruct->status_saat_ini : null;
                $isConfirmedStruct = $existingKPStruct && $existingKPStruct->dikonfirmasi_at;

                if ($currentStructStatus === 'Upload E-HRM' || $isConfirmedStruct) {
                    $statusStruktural = 'Upload E-HRM';
                    $keteranganStruktural = 'TTE Selesai. Menunggu upload SK E-HRM.';
                } elseif ($currentStructStatus === 'Proses') {
                    $statusStruktural    = 'Proses';
                    $keteranganStruktural = 'Sedang diproses admin';
                } elseif ($idxGolru === false || $idxMin === false || $idxMax === false) {
                    $statusStruktural = 'Aman';
                    $keteranganStruktural = 'Data golongan ruang tidak dikenali dalam referensi';
                } elseif ($idxGolru >= $idxMax) {
                    $statusStruktural    = 'Aman';
                    $keteranganStruktural = 'Sudah mencapai Puncak Golongan Ruang untuk Eselon saat ini';
                } elseif (!$tmtJabatan) {
                    $statusStruktural = 'Aman';
                    $keteranganStruktural = 'Data TMT Struktural / Pelantikan tidak tersedia';
                } else {
                    $tmtPangkat = $pegawai->tmt_pangkat_terakhir ? Carbon::parse($pegawai->tmt_pangkat_terakhir) : null;
                    $tmtJabatanCarbon = Carbon::parse($tmtJabatan);

                    if (!$tmtPangkat) {
                        $statusStruktural = 'Aman';
                        $keteranganStruktural = 'Data TMT Pangkat tidak tersedia';
                    } else {
                        $isNewAppointment = $tmtPangkat->lt($tmtJabatanCarbon);

                        if ($isNewAppointment) {
                            if ($masaPangkat >= 4) {
                                $statusStruktural = 'Usulan';
                                $keteranganStruktural = "Alasan kami mengajukan kenaikan pangkat untuk pegawai {$pegawai->nama} adalah kondisi Memenuhi Syarat (Baru Diangkat & Pangkat Terakhir >= 4 Tahun)";
                                $tanggalTargetStruktural = $today; 
                            } else {
                                $tanggalTargetStruktural = $tmtJabatanCarbon->copy()->addYear();
                                $startNotify = $tanggalTargetStruktural->copy()->subDays(60);
                                if ($today->greaterThanOrEqualTo($startNotify)) {
                                    $statusStruktural = 'Usulan';
                                    $keteranganStruktural = "Alasan kami mengajukan kenaikan pangkat untuk pegawai {$pegawai->nama} adalah kondisi Memenuhi Syarat (Struktural 1 Tahun dari Pelantikan)";
                                } else {
                                    $statusStruktural = 'Aman';
                                    $keteranganStruktural = 'Menunggu 1 tahun dari pelantikan (Target: ' . $tanggalTargetStruktural->format('d-m-Y') . ')';
                                }
                            }
                        } else {
                            $tanggalTargetStruktural = $tmtPangkat->copy()->addYears(4);
                            $startNotify = $tanggalTargetStruktural->copy()->subDays(60);
                            if ($today->greaterThanOrEqualTo($startNotify)) {
                                $statusStruktural = 'Usulan';
                                $keteranganStruktural = "Alasan kami mengajukan kenaikan pangkat untuk pegawai {$pegawai->nama} adalah kondisi Memenuhi Syarat (Reguler 4 Tahun dari Pangkat Terakhir)";
                            } else {
                                $statusStruktural = 'Aman';
                                $keteranganStruktural = 'Menunggu 4 tahun dari pangkat terakhir (Target: ' . $tanggalTargetStruktural->format('d-m-Y') . ')';
                            }
                        }
                    }
                }

                if ($statusStruktural != 'Aman') {
                    $dokumenTeruploadStruktural = 0;
                    if ($statusStruktural !== 'Upload E-HRM') {
                        $uploadedNamesStruct = $existingKPStruct ? $existingKPStruct->kelengkapan_dokumen->where('is_uploaded', true)->pluck('nama_dokumen')->toArray() : [];
                        
                        if (!empty($pegawai->sk_pangkat_terakhir) || in_array("SK Pangkat Terakhir", $uploadedNamesStruct)) {
                            $dokumenTeruploadStruktural++;
                        }

                        $riwJabatanMatch = $pegawai->riwayat_jabatan
                            ? $pegawai->riwayat_jabatan
                                ->where('kd_eselon', $pegawai->kd_eselon)
                                ->whereNotNull('file_sk')
                                ->where('file_sk', '!=', '')
                                ->first()
                            : null;
                        
                        if ($riwJabatanMatch || in_array("SK Jabatan Terakhir", $uploadedNamesStruct)) {
                            $dokumenTeruploadStruktural++;
                        }
                    }

                    $trackerStruct = DashboardTracker::updateOrCreate(
                        [
                            'pegawai_id' => $pegawai->id_pegawai_api,
                            'kategori'   => 'KP_Struktural',
                        ],
                        [
                            'status_saat_ini'   => $statusStruktural,
                            'keterangan'        => $keteranganStruktural,
                            'dokumen_total'     => 2,
                            'dokumen_terupload' => $dokumenTeruploadStruktural,
                            'tanggal_target'    => $tanggalTargetStruktural
                                                    ? $tanggalTargetStruktural->format('Y-m-d')
                                                    : Carbon::now()->format('Y-m-d'),
                        ]
                    );
                    
                    if ($statusStruktural == 'Usulan' && !$trackerStruct->notified_at) {
                        $daftarUsulanBaru[] = [
                            'nama' => $pegawai->nama,
                            'nip' => $pegawai->nip,
                            'kategori' => 'KP_Struktural'
                        ];
                        $trackerStruct->update(['notified_at' => now()]);
                    }
                } else {
                    DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                        ->where('kategori', 'KP_Struktural')
                        ->delete();
                }
            }
        }
    }

    private function processReguler(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru): void
    {
        // Skip dummy/test data as they don't need reguler calculations
        if (str_contains(strtolower($pegawai->id_pegawai_api ?? ''), 'dummy') || 
            str_contains(strtolower($pegawai->nip ?? ''), 'dummy')) {
            return;
        }

        $tipeJabatanReg = strtolower(trim($pegawai->tipe_jabatan ?? ''));
        $isPelaksana = in_array($tipeJabatanReg, ['pelaksana', 'reguler', 'jabatan pelaksana']) || 
                       (empty($tipeJabatanReg) && empty($pegawai->kd_eselon) && empty($pegawai->jenjang));

        // Khusus: Jabatan Lainnya (Karyasiswa) yang sedang Tubel aktif diperlakukan sebagai Reguler
        if ($tipeJabatanReg === 'jabatan lainnya') {
            $riwayatTubel = \App\Models\RiwayatTubel::where('nip', $pegawai->nip)->get();
            $tubelAktif = $riwayatTubel->first(function ($t) use ($today) {
                if (!$t->tanggal_mulai) return false;
                
                /** @var Carbon $tanggalMulai */
                $tanggalMulai = $t->tanggal_mulai;
                if ($today->lt($tanggalMulai)) return false;
                
                /** @var Carbon|null $selesai */
                $selesai = $t->perpanjangan2_tanggal_mulai
                    ?? $t->perpanjangan1_tanggal_mulai
                    ?? $t->tanggal_selesai;
                    
                if ($selesai && $today->gt($selesai)) return false;
                return true;
            });
            if ($tubelAktif) {
                $isPelaksana = true;
            }
        }

        if ($isPelaksana && $pegawai->tmt_pangkat_terakhir) {
            $golruOrder = [
                'I/a', 'I/b', 'I/c', 'I/d',
                'II/a', 'II/b', 'II/c', 'II/d',
                'III/a', 'III/b', 'III/c', 'III/d',
                'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e',
            ];

            $currentGolru = trim($pegawai->pangkat_golongan);
            $maxGolru = $this->getMaxGolonganReguler($pegawai->jenjang_pendidikan);

            $currentIdx = array_search($currentGolru, $golruOrder);
            $maxIdx = array_search($maxGolru, $golruOrder);

            // Jika pangkat saat ini sudah mencapai atau melebihi batas pendidikan, lewati usulan KP Reguler
            if ($currentIdx !== false && $maxIdx !== false && $currentIdx >= $maxIdx) {
                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                    ->where('kategori', 'KP_Reguler')
                    ->delete();
                return;
            }

            $tmtPangkat = Carbon::parse($pegawai->tmt_pangkat_terakhir);
            $masaPangkatReguler = (int) $tmtPangkat->diffInMonths($today);
            $tahun = intdiv($masaPangkatReguler, 12);
            $bulan = $masaPangkatReguler % 12;
            $masaKerjaLabel = "{$tahun} Tahun {$bulan} Bulan";

            $tanggalTargetReguler = $tmtPangkat->copy()->addYears(4);

            $statusReguler    = 'Aman';
            $keteranganReguler = $masaKerjaLabel;

            $existingKPReguler = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                ->where('kategori', 'KP_Reguler')
                ->first();

            $currentRegulerStatus = $existingKPReguler ? $existingKPReguler->status_saat_ini : null;
            $isConfirmedReguler   = $existingKPReguler && $existingKPReguler->dikonfirmasi_at;

            if ($currentRegulerStatus === 'Upload E-HRM' || $isConfirmedReguler) {
                $statusReguler = 'Upload E-HRM';
                $keteranganReguler = 'TTE Selesai. Menunggu upload SK E-HRM.';
            } elseif ($currentRegulerStatus === 'Proses') {
                $statusReguler    = 'Proses';
                $keteranganReguler = "Sedang diproses admin ({$masaKerjaLabel})";
            } elseif ($masaPangkatReguler >= 48) {
                $skpResult = $this->checkSkpCompliance($pegawai);
                if ($skpResult['meet']) {
                    $statusReguler    = 'Usulan';
                    $keteranganReguler = $masaKerjaLabel;
                } else {
                    $statusReguler    = 'Aman';
                    $keteranganReguler = 'Kurang SKP: ' . $skpResult['reason'];
                }
            }

            if ($statusReguler == 'Aman') {
                $this->sendSkpTriwulanReminder($pegawai);
                DashboardTracker::updateOrCreate(
                    [
                        'pegawai_id' => $pegawai->id_pegawai_api,
                        'kategori'   => 'KP_Reguler',
                    ],
                    [
                        'status_saat_ini'   => 'Aman',
                        'keterangan'        => $keteranganReguler,
                        'dokumen_total'     => 1,
                        'dokumen_terupload' => 0,
                    ]
                );
            } else {
                $dokumenTeruploadReguler = 0;
                if ($statusReguler !== 'Upload E-HRM') {
                    $uploadedNamesReguler = $existingKPReguler ? $existingKPReguler->kelengkapan_dokumen->where('is_uploaded', true)->pluck('nama_dokumen')->toArray() : [];
                    
                    if (!empty($pegawai->sk_pangkat_terakhir) || in_array("SK Pangkat Terakhir", $uploadedNamesReguler)) {
                        $dokumenTeruploadReguler++;
                    }
                }

                $trackerReg = DashboardTracker::updateOrCreate(
                    [
                        'pegawai_id' => $pegawai->id_pegawai_api,
                        'kategori'   => 'KP_Reguler',
                    ],
                    [
                        'status_saat_ini'   => $statusReguler,
                        'keterangan'        => $keteranganReguler,
                        'dokumen_total'     => 1,
                        'dokumen_terupload' => $dokumenTeruploadReguler,
                        'tanggal_target'  => $tanggalTargetReguler->format('Y-m-d'),
                    ]
                );

                if ($statusReguler == 'Usulan' && !$trackerReg->notified_at) {
                    $daftarUsulanBaru[] = [
                        'nama' => $pegawai->nama,
                        'nip' => $pegawai->nip,
                        'kategori' => 'KP_Reguler'
                    ];
                    $trackerReg->update(['notified_at' => now()]);
                }
            }
        } else {
            DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                ->where('kategori', 'KP_Reguler')
                ->delete();
        }
    }

    private function sendSkpTriwulanReminder(Pegawai $pegawai): void
    {
        $notifCacheKey = 'skp_triwulan_notif_' . $pegawai->id_pegawai_api;
        if (!Cache::has($notifCacheKey)) {
            $rule = \App\Models\NotifikasiRules::where('kategori', 'Notifikasi Triwulan')->first();
            if ($rule) {
                $notifiable = User::where('email', $pegawai->email)->first();
                if (!$notifiable && $pegawai->email) {
                    $notifiable = Notification::route('mail', $pegawai->email);
                }
                if ($notifiable) {
                    $pesan = str_replace(
                        ['{nip}', '{deadline}'],
                        [$pegawai->nip, Carbon::now()->addDays(7)->format('d-m-Y')],
                        $rule->template_pesan
                    );
                    try {
                        $notifiable->notify(new SystemAlertNotification($pegawai, "🔔 Pengingat Update SKP", $pesan));
                        Cache::put($notifCacheKey, true, now()->addDays(90));
                        ActivityLogger::logSystem("Mengirim notifikasi pengingat SKP ke pegawai {$pegawai->nama}", $pegawai->nip);
                    } catch (\Exception $e) {}
                }
            }
        }
    }

    private function sendUploadEhrmNotification(Pegawai $pegawai, string $kategori, DashboardTracker $tracker, array $dokumenWajib): void
    {
        $notifCacheKey = 'upload_ehrm_notif_' . $pegawai->id_pegawai_api . '_' . $kategori;
        if (!Cache::has($notifCacheKey)) {
            $rule = \App\Models\NotifikasiRules::where('kategori', $kategori . ' Upload Dokumen')->first();
            if ($rule) {
                $notifiable = User::where('email', $pegawai->email)->first();
                if (!$notifiable && $pegawai->email) {
                    $notifiable = Notification::route('mail', $pegawai->email);
                }
                if ($notifiable) {
                    $uploadedNames = $tracker->kelengkapan_dokumen->where('is_uploaded', true)->pluck('nama_dokumen')->toArray();
                    $missingDocs = [];
                    $targetPangkat = $pegawai->pangkat_golongan ? (
                        ['I/a'=>'I/b','I/b'=>'I/c','I/c'=>'I/d','I/d'=>'II/a',
                         'II/a'=>'II/b','II/b'=>'II/c','II/c'=>'II/d','II/d'=>'III/a',
                         'III/a'=>'III/b','III/b'=>'III/c','III/c'=>'III/d','III/d'=>'IV/a',
                         'IV/a'=>'IV/b','IV/b'=>'IV/c','IV/c'=>'IV/d','IV/d'=>'IV/e'][$pegawai->pangkat_golongan] ?? 'Baru'
                    ) : 'Baru';

                    foreach ($dokumenWajib as $doc) {
                        if (!in_array($doc, $uploadedNames)) {
                            if ($doc == 'SK Pangkat Terakhir') {
                                $missingDocs[] = "- SK Pangkat Baru (Tujuan: {$targetPangkat})";
                            } elseif ($doc == 'SK Jabatan Terakhir' || $doc == 'SK Jabatan Fungsional Terakhir') {
                                $missingDocs[] = "- SK Jabatan Baru";
                            } else {
                                $missingDocs[] = "- " . $doc;
                            }
                        }
                    }

                    if (empty($missingDocs)) {
                        return; 
                    }

                    $missingStr = implode("\n", $missingDocs);
                    $pesan = str_replace('{missing_documents}', $missingStr, $rule->template_pesan);

                    try {
                        $notifiable->notify(new SystemAlertNotification($pegawai, "📋 Permintaan Upload Dokumen " . str_replace('_', ' ', $kategori), $pesan));
                        Cache::put($notifCacheKey, true, now()->addDays(1)); 
                        ActivityLogger::logSystem("Mengirim notifikasi Upload E-HRM ke pegawai {$pegawai->nama} ({$kategori})", $pegawai->nip);
                    } catch (\Exception $e) {}
                }
            }
        }
    }

    /**
     * Mendapatkan golongan ruang tertinggi yang diperbolehkan untuk KP Reguler berdasarkan pendidikan.
     */
    private function getMaxGolonganReguler(?string $education): string
    {
        if (!$education) {
            return 'IV/e'; // Default fallback jika data tidak ada
        }
        
        $education = strtoupper(trim($education));
        
        if (str_contains($education, 'S3') || str_contains($education, 'DOKTOR')) {
            return 'IV/b';
        }
        if (str_contains($education, 'S2') || str_contains($education, 'MAGISTER')) {
            return 'IV/a';
        }
        if (str_contains($education, 'S1') || str_contains($education, 'D4') || str_contains($education, 'D-IV') || str_contains($education, 'SARJANA')) {
            return 'III/d';
        }
        if (str_contains($education, 'D3') || str_contains($education, 'D-III') || str_contains($education, 'SM/') || str_contains($education, 'SARJANA MUDA')) {
            return 'III/c';
        }
        if (str_contains($education, 'D2') || str_contains($education, 'D-II') || str_contains($education, 'DIPLOMA II')) {
            return 'III/b';
        }
        if (str_contains($education, 'SLTA') || str_contains($education, 'SMA') || str_contains($education, 'SMK') || str_contains($education, 'SLTA/')) {
            return 'III/b';
        }
        if (str_contains($education, 'SLTP') || str_contains($education, 'SMP')) {
            return 'II/c';
        }
        if (str_contains($education, 'SD')) {
            return 'II/a';
        }
        
        return 'IV/e';
    }
}