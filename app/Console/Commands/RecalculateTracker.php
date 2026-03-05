<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\User; // Butuh User untuk kirim notif
use Illuminate\Support\Facades\Notification;
use App\Notifications\KgbMendekatiNotification;
use App\Notifications\KgbUsulanNotification;
use App\Models\NotifikasiRules;
use Carbon\Carbon;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Cache;

class RecalculateTracker extends Command
{
    protected $signature = 'tracker:run {--force : Paksa kirim notifikasi tanpa cek interval}';
    protected $description = 'Hitung ulang status KGB dan Pangkat/Jenjang (berdasarkan Triwulan) serta Kirim Notifikasi';

    public function handle()
    {
        $this->info('⚙️  Memulai perhitungan tracker & notifikasi...');
        ActivityLogger::logSystem('Memulai perhitungan tracker & pengiriman notifikasi');
        
        // --- AMBIL CONFIG RULE DARI DB ---
        $rulePenjadwalan = NotifikasiRules::where('kategori', 'KGB Penjadwalan')->first();
        $ruleUpload      = NotifikasiRules::where('kategori', 'KGB Upload Dokumen')->first();

        // Default: 60 hari (2 bulan) untuk lead time, 1 hari untuk frekuensi
        $leadCheckDays = $rulePenjadwalan ? $rulePenjadwalan->interval_hari : 60;
        $freqUploadDays = $ruleUpload ? ($ruleUpload->interval_hari > 0 ? $ruleUpload->interval_hari : 1) : 1; 

        // CACHE: Muat seluruh isi matriks jf ke memory untuk performa (Fase 3: Big Data Optimized)
        $matriksKamus = Cache::remember('ref_matriks_jf_all', 3600, function () {
            return \App\Models\RefMatriksJf::all();
        });

        $count = Pegawai::count();
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // Big Data Optimized: Menggunakan chunkById(500) dan eager loading untuk menghindari N+1 Query Problem
        Pegawai::with(['riwayatAngkaKredit' => function ($query) {
            $query->orderBy('tmt_angka_kredit', 'desc');
        }])->chunkById(500, function ($pegawais) use ($bar, $matriksKamus, $leadCheckDays, $freqUploadDays) {
            foreach ($pegawais as $pegawai) {
                $today = Carbon::now();

                // ==========================================
                // LOGIKA KGB
                // ==========================================
                if ($pegawai->tmt_kgb_terakhir) {
                    $nextKGB = Carbon::parse($pegawai->tmt_kgb_terakhir)->addYears(2);
                    $startNotify = $nextKGB->copy()->subDays($leadCheckDays);

                    $status = 'Aman';
                    $keterangan = 'Masih dalam masa aman';

                    $existingTracker = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                        ->where('kategori', 'KGB')
                                        ->first();
                    
                    $isConfirmed = $existingTracker && $existingTracker->dikonfirmasi_at;
                    $isUploaded  = $existingTracker && ($existingTracker->dokumen_terupload >= $existingTracker->dokumen_total);
                    
                    if ($today->greaterThanOrEqualTo($nextKGB)) {
                        if ($isUploaded) {
                            $status = 'Aman';
                        } else {
                            $status = 'Upload E-HRM';
                            $keterangan = 'SK KGB sudah terbit? Silakan upload E-HRM.';
                        }
                    } elseif ($today->greaterThanOrEqualTo($startNotify)) {
                        if ($isConfirmed) {
                            $status = 'Proses';
                            $keterangan = 'Usulan sedang diproses oleh admin.';
                        } else {
                            $status = 'Usulan';
                            $keterangan = 'Segera ajukan usulan KGB.';
                        }
                    }

                    if ($status != 'Aman') {
                        $tracker = DashboardTracker::updateOrCreate(
                            ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'KGB'],
                            [
                                'tanggal_target'  => $nextKGB->format('Y-m-d'),
                                'status_saat_ini' => $status,
                                'keterangan'      => $keterangan,
                                'dokumen_total'   => 1,
                            ]
                        );

                        if ($tracker->wasChanged('status_saat_ini')) {
                            if ($status == 'Upload E-HRM' || $status == 'Usulan') {
                                $tracker->update(['notified_at' => null]);
                            }
                        }

                        $lastNotifDate = $tracker->notified_at ? Carbon::parse($tracker->notified_at) : null;
                        $daysSinceLast = $lastNotifDate ? $lastNotifDate->diffInDays($today) : 9999;
                        
                        $isDueForUploadNotif = ($daysSinceLast >= $freqUploadDays);
                        
                        if ($this->option('force')) {
                            $isDueForUploadNotif = true;
                        } else {
                            if ($lastNotifDate && $lastNotifDate->isToday()) {
                                $isDueForUploadNotif = false;
                            }
                        }

                        if ($status == 'Usulan' && !$tracker->notified_at) {
                            $admins = User::whereIn('role', ['super_admin', 'admin_pegawai'])->get();
                            if ($admins->count() > 0) {
                                Notification::send($admins, new KgbMendekatiNotification($pegawai));
                                $tracker->update(['notified_at' => now()]);
                                ActivityLogger::logSystem("Mengirim notifikasi KGB Usulan ke admin untuk pegawai {$pegawai->nama}", $pegawai->nip);
                            }
                        } elseif ($status == 'Upload E-HRM') {
                            if ($isDueForUploadNotif) {
                                $notifiable = User::where('email', $pegawai->email)->first();
                                if (!$notifiable && $pegawai->email) {
                                    $notifiable = Notification::route('mail', $pegawai->email);
                                }
                                if ($notifiable) {
                                    $notifiable->notify(new KgbUsulanNotification($tracker));
                                    $tracker->update(['notified_at' => now()]); 
                                    ActivityLogger::logSystem("Mengirim notifikasi KGB Upload E-HRM ke pegawai {$pegawai->nama}", $pegawai->nip);
                                }
                            }
                        }
                    } else {
                        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                        ->where('kategori', 'KGB')
                                        ->delete();
                    }
                }

                // ==========================================
                // LOGIKA AK FUNGSIONAL & UKOM (Berdasarkan Proyeksi Triwulan)
                // ==========================================
                // Syarat kinerja logis: pegawai Fungsional
                if (!empty($pegawai->pangkat_golongan) && !empty($pegawai->jabatan_saat_ini)) { 
                    // Kita tidak menggunakan where('tipe_jabatan', 'Fungsional') dengan ketat karena ada kemungkinan 
                    // pegawai dummy belum diset tipe jabatannya. Namun ref_matriks_jf hanya mengakomodir jabatan Fungsional.
                    // Jika ingin ketat, buka komen: if ($pegawai->tipe_jabatan == 'Fungsional') 

                    // Normalisasi case karena API bisa mengembalikan "AHLI MADYA" (all caps) sedangkan matriks kita "Ahli Madya"
                    $normalizedJenjang = ucwords(strtolower(trim($pegawai->jenjang)));
                    
                    // Penentuan Kategori berdasarkan matriks di Memory (Cache) 
                    $matriks = $matriksKamus->first(function ($item) use ($normalizedJenjang, $pegawai) {
                        return strtolower($item->jabatan_asal) === strtolower($normalizedJenjang) && 
                               strtolower($item->pangkat_asal) === strtolower(trim($pegawai->pangkat_golongan));
                    });

                    if ($matriks) {
                        // KEBUTUHAN AK KUMULATIF:
                        // AK direset ke-0 HANYA saat kenaikan JENJANG. Saat kenaikan PANGKAT (dalam jenjang yg sama), AK berlanjut.
                        // TRANSISI: Baris dengan target_ak=0 artinya sudah naik jenjang, langsung qualify tanpa AK tambahan.
                        if ($matriks->target_ak == 0) {
                            $targetAK = 0;
                        } else {
                            $targetAK = $matriksKamus->where('jabatan_asal', $matriks->jabatan_asal)
                                                     ->where('id', '<=', $matriks->id)
                                                     ->sum('target_ak');
                        }
                                                 
                        $koefisienTahunan = $matriks->koefisien_tahunan ?? 0;
                        $isKenaikanJenjang = $matriks->is_naik_jenjang;
                        
                        // Kategori KJ_Jafung = KJ/UKOM, kategori KP_Jafung = Kenaikan Pangkat (dalam jenjang yang sama)
                        $kategoriSekarang = $isKenaikanJenjang ? 'KJ_Jafung' : 'KP_Jafung';
                        $kategoriLawan = $isKenaikanJenjang ? 'KP_Jafung' : 'KJ_Jafung';
                        $dokumenTotal = $isKenaikanJenjang ? 3 : 2;
                        
                        $namaProses = $isKenaikanJenjang ? 'Kenaikan Jenjang' : 'Kenaikan Pangkat';
                        $tujuanProses = $isKenaikanJenjang ? ($matriks->next_jenjang ?? 'Jenjang Berikutnya') : ($matriks->next_pangkat ?? 'Pangkat Berikutnya');

                        // Ambil 1 Riwayat AK terbaru secara Eager Loaded (sudah discore order desc)
                        $latestAK = $pegawai->riwayatAngkaKredit->first();
                        $currentAK = 0;

                        // Filter AK Valid: Harus lebih besar dari `tmt_pangkat_terakhir`
                        if ($latestAK && $pegawai->tmt_pangkat_terakhir) {
                            $tmtAK = Carbon::parse($latestAK->tmt_angka_kredit);
                            $tmtPangkat = Carbon::parse($pegawai->tmt_pangkat_terakhir);
                            
                            if ($tmtAK->greaterThan($tmtPangkat)) {
                                $currentAK = $latestAK->total_kredit;
                            }
                        } elseif ($latestAK && empty($pegawai->tmt_pangkat_terakhir)) {
                            // Anggap valid jika tmt pangkat terakhir belum terdokumentasi
                            $currentAK = $latestAK->total_kredit;
                        }

                        if ($targetAK > 0) {
                            // Hitung Proyeksi Triwulan (Business Logic)
                            $kekuranganAK = $targetAK - $currentAK;
                            
                            $akTriwulanBaik = ($koefisienTahunan / 4) * 1.0;
                            $akTriwulanSangatBaik = ($koefisienTahunan / 4) * 1.5;

                            $statusAK = '';
                            $keteranganAK = '';
                            $kurangFormat = number_format($kekuranganAK, 3, ',', '.'); 

                            $existingAK = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                ->whereIn('kategori', [$kategoriSekarang, 'UKOM'])
                                ->first();

                            $isProses = $existingAK && (
                                $existingAK->status_saat_ini === 'Proses' || 
                                $existingAK->dikonfirmasi_at ||
                                $existingAK->kategori === 'UKOM'
                            );

                            $skipTrackerUpdate = false;

                            // Penentuan Status Tracker
                            if ($isProses) {
                                $statusAK = 'Proses';
                                $keteranganAK = 'Sedang diproses admin';
                                if ($existingAK->kategori === 'UKOM') {
                                    $skipTrackerUpdate = true;
                                }
                            } elseif (is_null($latestAK) || $currentAK == 0) {
                                // Jika data AK tidak ada atau nilainya 0, maka belum memenuhi syarat untuk dihitung KP
                                $statusAK = 'Data Tidak Lengkap'; 
                                $keteranganAK = 'Peringatan: Data Riwayat AK tidak ditemukan di e-HRM atau bernilai 0. Segera upload/update SK PAK Anda.';
                            } else {
                                if ($kekuranganAK <= 0) {
                                    $statusAK = 'Usulan';
                                    $keteranganAK = "AK memenuhi target. Segera usulkan {$namaProses} ke {$tujuanProses}";
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

                            // Tentukan tanggal target notifikasi (saat AK mencukupi / mendekati)
                            $targetDate = null;
                            if (in_array($statusAK, ['Usulan', 'Mendekati', 'Proses'])) {
                                if ($existingAK && $existingAK->tanggal_target) {
                                    $targetDate = $existingAK->tanggal_target;
                                } else {
                                    $targetDate = Carbon::now()->format('Y-m-d');
                                }
                            }

                            // Manajemen Database Tracker
                            if (!$skipTrackerUpdate) {
                                if (in_array($statusAK, ['Usulan', 'Mendekati', 'Proses'])) {
                                    DashboardTracker::updateOrCreate(
                                        [
                                            'pegawai_id' => $pegawai->id_pegawai_api,
                                            'kategori'   => $kategoriSekarang,
                                        ],
                                        [
                                            'status_saat_ini' => $statusAK,
                                            'keterangan'      => $keteranganAK,
                                            'dokumen_total'   => $dokumenTotal,
                                            'tanggal_target'  => $targetDate,
                                        ]
                                    );

                                    // Hapus Kategori Lawan
                                    DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                        ->where('kategori', $kategoriLawan)
                                        ->delete();
                                } else {
                                    // Status Aman -> Hapus keduanya + UKOM agar tidak jadi Zombie Tracker
                                    DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                        ->whereIn('kategori', ['KP_Jafung', 'KJ_Jafung', 'UKOM'])
                                        ->delete();
                                }
                            }
                        }
                    }
                }

                // ==========================================
                // LOGIKA KP STRUKTURAL
                // ==========================================
                // Syarat kinerja logis: pegawai Struktural
                if (!empty($pegawai->pangkat_golongan) && !empty($pegawai->kd_eselon)) {
                    // Mapping Eselon ke Min & Max Golru
                    $eselonMapping = [
                        '1' => ['min' => 'IV/d', 'max' => 'IV/e'], // I/a
                        '2' => ['min' => 'IV/c', 'max' => 'IV/e'], // I/b
                        '3' => ['min' => 'IV/c', 'max' => 'IV/d'], // II/a
                        '4' => ['min' => 'IV/b', 'max' => 'IV/c'], // II/b
                        '5' => ['min' => 'IV/a', 'max' => 'IV/b'], // III/a
                        '6' => ['min' => 'III/d', 'max' => 'IV/a'], // III/b
                        '7' => ['min' => 'III/c', 'max' => 'III/d'], // IV/a
                        '8' => ['min' => 'III/b', 'max' => 'III/c'], // IV/b
                        '9' => ['min' => 'III/a', 'max' => 'III/b'], // V
                    ];

                    // -------------------------------------------------------
                    // FIX: Urutan golongan ruang ASN yang benar (I/a = terendah, IV/e = tertinggi)
                    // Tidak bisa pakai strcmp() → 'IV/a' < 'III/d' secara ASCII karena 'V' > 'I'
                    // Solusi: bandingkan berdasarkan index posisi dalam array berikut
                    // -------------------------------------------------------
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

                        // --- HITUNG TANGGAL TARGET (tmt_pangkat_terakhir + 4 tahun) ---
                        $tanggalTargetStruktural = null;
                        if ($pegawai->tmt_pangkat_terakhir) {
                            $tanggalTargetStruktural = Carbon::parse($pegawai->tmt_pangkat_terakhir)->addYears(4);
                        }

                        // --- FILTER H-2 BULAN (60 hari sebelum tanggal target) ---
                        $startNotifyStruktural = $tanggalTargetStruktural
                            ? $tanggalTargetStruktural->copy()->subDays(60)
                            : null;

                        $inNotifyWindow = $startNotifyStruktural && $today->greaterThanOrEqualTo($startNotifyStruktural);

                        // Syarat Mutlak: Masa Pangkat >= 1 thn AND Masa Jabatan >= 1 thn
                        $masaPangkat = 0;
                        $masaJabatan = 0;

                        if ($pegawai->tmt_pangkat_terakhir) {
                            $masaPangkat = Carbon::parse($pegawai->tmt_pangkat_terakhir)->diffInYears($today);
                        }

                        // tmt_struktural → fallback ke riwayat_jabatan terakhir
                        $tmtJabatan = $pegawai->tmt_struktural;
                        if (!$tmtJabatan && $pegawai->riwayat_jabatan) {
                            $latestJabatan = $pegawai->riwayat_jabatan->first();
                            if ($latestJabatan && $latestJabatan->tmt_jabatan) {
                                $tmtJabatan = $latestJabatan->tmt_jabatan;
                            }
                        }

                        if ($tmtJabatan) {
                            $masaJabatan = Carbon::parse($tmtJabatan)->diffInYears($today);
                        }

                        $statusStruktural    = 'Aman';
                        $keteranganStruktural = 'Masih dalam masa aman / Belum memenuhi masa kerja';

                        $existingKPStruct = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                            ->where('kategori', 'KP_Struktural')
                            ->first();

                        $isProsesStruct = $existingKPStruct && (
                            $existingKPStruct->status_saat_ini === 'Proses' ||
                            $existingKPStruct->dikonfirmasi_at
                        );

                        if ($isProsesStruct) {
                            $statusStruktural    = 'Proses';
                            $keteranganStruktural = 'Sedang diproses admin';

                        } elseif ($idxGolru === false || $idxMin === false || $idxMax === false) {
                            // Golru dari API tidak dikenali dalam referensi → lewati
                            $statusStruktural = 'Aman';
                            $keteranganStruktural = 'Data golongan ruang tidak dikenali dalam referensi';

                        } elseif ($idxGolru >= $idxMax) {
                            // Sudah di puncak pangkat untuk eselon ini → Aman
                            $statusStruktural    = 'Aman';
                            $keteranganStruktural = 'Sudah mencapai Puncak Golongan Ruang untuk Eselon saat ini';

                        } elseif (!$pegawai->tmt_pangkat_terakhir && $masaJabatan >= 1) {
                            // FALLBACK: tmt_pangkat NULL (API tidak punya data)
                            // Tapi masa jabatan >= 1 tahun dan golru belum puncak → tampilkan
                            $statusStruktural    = 'Usulan';
                            $keteranganStruktural = 'Memenuhi Syarat Kenaikan Pangkat Pilihan (Struktural). Data TMT Pangkat belum tersedia dari API.';

                        } elseif ($inNotifyWindow && $masaPangkat >= 1 && $masaJabatan >= 1) {
                            // Window H-2 bulan + syarat mutlak terpenuhi
                            $statusStruktural    = 'Usulan';
                            $keteranganStruktural = 'Memenuhi Syarat Kenaikan Pangkat Pilihan (Struktural)';
                        }
                        if ($statusStruktural != 'Aman') {
                            DashboardTracker::updateOrCreate(
                                [
                                    'pegawai_id' => $pegawai->id_pegawai_api,
                                    'kategori'   => 'KP_Struktural',
                                ],
                                [
                                    'status_saat_ini' => $statusStruktural,
                                    'keterangan'      => $keteranganStruktural,
                                    'dokumen_total'   => 2, // Asumsi 2 dokumen (SK Pangkat, SK Jabatan)
                                    'tanggal_target'  => $tanggalTargetStruktural
                                                            ? $tanggalTargetStruktural->format('Y-m-d')
                                                            : Carbon::now()->format('Y-m-d'),
                                ]
                            );
                        } else {
                            DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                ->where('kategori', 'KP_Struktural')
                                ->delete();
                        }
                    }
                }


                // ==========================================
                // LOGIKA KP REGULER (HANYA JABATAN PELAKSANA)
                // ==========================================
                // Pegawai Pelaksana yang masa pangkat >= 4 tahun → USULAN
                // Tidak perlu validasi SKP
                $isPelaksana = $pegawai->tipe_jabatan && str_contains(strtolower($pegawai->tipe_jabatan), 'pelaksana');
                if ($isPelaksana && $pegawai->tmt_pangkat_terakhir) {
                    $tmtPangkat = Carbon::parse($pegawai->tmt_pangkat_terakhir);
                    $masaPangkatReguler = (int) $tmtPangkat->diffInMonths($today);
                    $tahun = intdiv($masaPangkatReguler, 12);
                    $bulan = $masaPangkatReguler % 12;
                    $masaKerjaLabel = "{$tahun} Tahun {$bulan} Bulan";

                    $tanggalTargetReguler = $tmtPangkat->copy()->addYears(4);

                    $statusReguler    = 'Aman';
                    $keteranganReguler = $masaKerjaLabel;

                    // Cek apakah sudah dikonfirmasi admin
                    $existingKPReguler = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                        ->where('kategori', 'KP_Reguler')
                        ->first();

                    $isProsesReguler = $existingKPReguler && (
                        $existingKPReguler->status_saat_ini === 'Proses' ||
                        $existingKPReguler->dikonfirmasi_at
                    );

                    if ($isProsesReguler) {
                        $statusReguler    = 'Proses';
                        $keteranganReguler = "Sedang diproses admin ({$masaKerjaLabel})";
                    } elseif ($masaPangkatReguler >= 48) {
                        // Masa pangkat >= 4 tahun (48 bulan) → USULAN
                        $statusReguler    = 'Usulan';
                        $keteranganReguler = $masaKerjaLabel;
                    }

                    if ($statusReguler != 'Aman') {
                        DashboardTracker::updateOrCreate(
                            [
                                'pegawai_id' => $pegawai->id_pegawai_api,
                                'kategori'   => 'KP_Reguler',
                            ],
                            [
                                'status_saat_ini' => $statusReguler,
                                'keterangan'      => $keteranganReguler,
                                'dokumen_total'   => 0,
                                'tanggal_target'  => $tanggalTargetReguler->format('Y-m-d'),
                            ]
                        );
                    } else {
                        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                            ->where('kategori', 'KP_Reguler')
                            ->delete();
                    }
                } else {
                    // Bukan pelaksana atau gak punya tmt_pangkat → hapus KP_Reguler jika ada
                    DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                        ->where('kategori', 'KP_Reguler')
                        ->delete();
                }

                // ==========================================
                // LOGIKA MONITORING DIKLAT
                // ==========================================
                $riwayatDiklat = \App\Models\RiwayatDiklat::where('nip', $pegawai->nip)->get();

                if ($riwayatDiklat->isNotEmpty()) {
                    // --- HUTANG LAPORAN: status_diklat=0 AND tanggal_selesai sudah lewat ---
                    $hutangDiklat = $riwayatDiklat->filter(function ($d) use ($today) {
                        return $d->status_diklat == 0
                            && $d->tanggal_selesai
                            && $today->greaterThan(Carbon::parse($d->tanggal_selesai));
                    });

                    if ($hutangDiklat->isNotEmpty()) {
                        $jumlahHutang = $hutangDiklat->count();
                        $keterangan = $jumlahHutang == 1
                            ? 'Sertifikat diklat belum diupload ke E-HRM'
                            : $jumlahHutang . ' sertifikat diklat belum diupload ke E-HRM';

                        DashboardTracker::updateOrCreate(
                            ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'DIKLAT_HUTANG'],
                            [
                                'status_saat_ini' => 'Usulan',
                                'keterangan' => $keterangan,
                                'dokumen_total' => $jumlahHutang,
                                'dokumen_terupload' => 0,
                                'tanggal_target' => Carbon::parse($hutangDiklat->sortBy('tanggal_selesai')->first()->tanggal_selesai)->format('Y-m-d'),
                            ]
                        );
                    } else {
                        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                            ->where('kategori', 'DIKLAT_HUTANG')->delete();
                    }

                    // --- ANOMALI DOKUMEN: status_diklat=1 tapi arsip/nomor_sertifikat null ---
                    $anomaliDiklat = $riwayatDiklat->filter(function ($d) {
                        return $d->status_diklat == 1
                            && (empty($d->arsip) || empty($d->nomor_sertifikat) || $d->nomor_sertifikat === '-');
                    });

                    if ($anomaliDiklat->isNotEmpty()) {
                        $jumlahAnomali = $anomaliDiklat->count();
                        $keterangan = $jumlahAnomali == 1
                            ? 'Dokumen diklat belum lengkap di E-HRM'
                            : $jumlahAnomali . ' dokumen diklat belum lengkap di E-HRM';

                        DashboardTracker::updateOrCreate(
                            ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'DIKLAT_ANOMALI'],
                            [
                                'status_saat_ini' => 'Usulan',
                                'keterangan' => $keterangan,
                                'dokumen_total' => $jumlahAnomali,
                                'dokumen_terupload' => 0,
                                'tanggal_target' => Carbon::now()->format('Y-m-d'),
                            ]
                        );
                    } else {
                        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                            ->where('kategori', 'DIKLAT_ANOMALI')->delete();
                    }
                } else {
                    // Tidak punya riwayat diklat → bersihkan tracker diklat
                    DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                        ->whereIn('kategori', ['DIKLAT_HUTANG', 'DIKLAT_ANOMALI'])->delete();
                }

                $bar->advance();
            }
        }, 'id_pegawai_api'); 

        $bar->finish();
        $this->newLine();
        $this->info('✅ Tracker update & Notifikasi terkirim!');
        ActivityLogger::logSystem('Perhitungan tracker selesai untuk ' . $count . ' pegawai');
    }
}