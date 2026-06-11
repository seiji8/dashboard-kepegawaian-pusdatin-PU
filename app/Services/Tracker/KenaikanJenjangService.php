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

class KenaikanJenjangService implements TrackerInterface
{
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        // Skip dummy/test data as they are manually seeded and don't have real Angka Kredit history
        if (str_contains(strtolower($pegawai->id_pegawai_api ?? ''), 'dummy') || 
            str_contains(strtolower($pegawai->nip ?? ''), 'dummy')) {
            return;
        }

        $matriksKamus = $context['matriksKamus'] ?? collect();
        
        $tipeJabatan = strtolower(trim($pegawai->tipe_jabatan ?? ''));
        $isFungsional = in_array($tipeJabatan, ['fungsional', 'jafung', 'jabatan fungsional']) || 
                        (!empty($pegawai->jenjang) && empty($tipeJabatan));

        if ($isFungsional && !empty($pegawai->pangkat_golongan) && !empty($pegawai->jabatan_saat_ini)) {
            $normalizedJenjang = ucwords(strtolower(trim($pegawai->jenjang)));
            
            $matriks = $matriksKamus->first(function ($item) use ($normalizedJenjang, $pegawai) {
                return strtolower($item->jabatan_asal) === strtolower($normalizedJenjang) && 
                       strtolower($item->pangkat_asal) === strtolower(trim($pegawai->pangkat_golongan));
            });

            if ($matriks && $matriks->is_naik_jenjang) {
                $targetAK = 0;
                if ($matriks->target_ak > 0) {
                    $targetAK = $matriksKamus->where('jabatan_asal', $matriks->jabatan_asal)
                                             ->where('id', '<=', $matriks->id)
                                             ->sum('target_ak');
                }
                                         
                $koefisienTahunan = $matriks->koefisien_tahunan ?? 0;
                
                $kategoriSekarang = 'KJ_Jafung';
                $kategoriLawan = 'KP_Jafung';
                $dokumenTotal = 2;
                
                $namaProses = 'Kenaikan Jenjang';
                $tujuanProses = $matriks->next_jenjang ?? 'Jenjang Berikutnya';

                $latestAK = $pegawai->riwayatAngkaKredit->first();
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
                        if ($existingAK->kategori === 'UKOM') $skipTrackerUpdate = true;
                    } elseif ($currentAKStatus === 'Proses') {
                        $statusAK = 'Proses';
                        $keteranganAK = 'Sedang diproses admin';
                        if ($existingAK->kategori === 'UKOM') {
                            $skipTrackerUpdate = true;
                        }
                    } elseif (is_null($latestAK) || $currentAK == 0) {
                        $statusAK = 'Data Tidak Lengkap'; 
                        $keteranganAK = 'Peringatan: Data Riwayat AK tidak ditemukan di e-HRM atau bernilai 0. Segera upload/update SK PAK Anda.';
                    } else {
                        if ($kekuranganAK <= 0) {
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

                            $sudahLulus = $existingAK && str_contains($existingAK->keterangan, 'Lulus UKOM');
                            if ($currentAKStatus === 'Usulan' && $sudahLulus) {
                                if ($skpMemenuhi) {
                                    $statusAK = 'Usulan';
                                    $keteranganAK = "Lulus UKOM dan SKP 2 Tahun Baik. Segera usulkan {$namaProses} ke {$tujuanProses}";
                                } else {
                                    $statusAK = 'Aman';
                                    $keteranganAK = "Lulus UKOM, namun " . $alasanSkp;
                                }
                            } else {
                                if ($skpMemenuhi) {
                                    $statusAK = 'Menunggu UKOM';
                                    $keteranganAK = "AK & SKP memenuhi target. Segera daftarkan Uji Kompetensi";
                                } else {
                                    $statusAK = 'Aman';
                                    $keteranganAK = "AK memenuhi target, namun " . $alasanSkp;
                                }
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
                        return;
                    }

                    $targetDate = null;
                    if (in_array($statusAK, ['Usulan', 'Proses', 'Menunggu UKOM'])) {
                        if ($existingAK && $existingAK->tanggal_target) {
                            $targetDate = $existingAK->tanggal_target;
                        } else {
                            $targetDate = Carbon::now()->format('Y-m-d');
                        }
                    }

                    if (!$skipTrackerUpdate) {
                        if (in_array($statusAK, ['Usulan', 'Proses', 'Menunggu UKOM', 'Upload E-HRM', 'Usulan Pengajuan', 'Proses TTE'])) {
                            $dokumenTerupload = 0;
                            if ($statusAK !== 'Upload E-HRM') {
                                if (!empty($pegawai->arsip_skp_2_tahun) && count($pegawai->arsip_skp_2_tahun) >= 2) {
                                    $dokumenTerupload++;
                                }
                                
                                $riwJabatanMatch = $pegawai->riwayat_jabatan
                                    ? $pegawai->riwayat_jabatan
                                        ->whereNotNull('file_sk')
                                        ->where('file_sk', '!=', '')
                                        ->sortByDesc('tmt_jabatan')
                                        ->first()
                                    : null;

                                $hasSkJabatan = false;
                                if ($existingAK) {
                                    $uploadedNames = $existingAK->kelengkapan_dokumen->where('is_uploaded', true)->pluck('nama_dokumen')->toArray();
                                    if (in_array("SK Jabatan Terakhir", $uploadedNames) || in_array("SK Jabatan Fungsional Terakhir", $uploadedNames)) {
                                        $hasSkJabatan = true;
                                    }
                                }
                                if ($riwJabatanMatch) {
                                    $hasSkJabatan = true;
                                }
                                if ($hasSkJabatan) {
                                    $dokumenTerupload++;
                                    
                                    // Fallback: update the KelengkapanDokumen record if it exists but is not uploaded
                                    if ($existingAK) {
                                        $docRecord = $existingAK->kelengkapan_dokumen->first(function ($d) {
                                            return $d->nama_dokumen === 'SK Jabatan Terakhir' || $d->nama_dokumen === 'SK Jabatan Fungsional Terakhir';
                                        });
                                        if ($docRecord && !$docRecord->is_uploaded) {
                                            $docRecord->update([
                                                'is_uploaded' => true,
                                                'link_file' => $riwJabatanMatch->file_sk ?? $docRecord->link_file
                                            ]);
                                        }
                                    }
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

                            if (in_array($statusAK, ['Usulan', 'Menunggu UKOM']) && !$tracker->notified_at) {
                                $daftarUsulanBaru[] = [
                                    'nama' => $pegawai->nama,
                                    'nip' => $pegawai->nip,
                                    'kategori' => $kategoriSekarang
                                ];
                                $tracker->update(['notified_at' => now()]);
                            }



                            DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                ->where('kategori', $kategoriLawan)
                                ->delete();
                        } else {
                            if ($statusAK == 'Aman') {
                                DashboardTracker::updateOrCreate(
                                    [
                                        'pegawai_id' => $pegawai->id_pegawai_api,
                                        'kategori'   => $kategoriSekarang,
                                    ],
                                    [
                                        'status_saat_ini'   => 'Aman',
                                        'keterangan'        => $keteranganAK,
                                        'dokumen_total'     => 1,
                                        'dokumen_terupload' => 0,
                                    ]
                                );
                            } else {
                                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                    ->whereIn('kategori', ['KP_Jafung', 'KJ_Jafung', 'UKOM'])
                                    ->delete();
                            }
                        }
                    }
                }
            }
        }
    }
}
