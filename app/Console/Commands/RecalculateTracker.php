<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\User; // Butuh User untuk kirim notif
use Illuminate\Support\Facades\Notification;
use App\Notifications\KgbMendekatiNotification;
use App\Notifications\SystemAlertNotification;
use App\Models\NotifikasiRules;
use Carbon\Carbon;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Cache;
use App\Mail\ManualNotification;
use Illuminate\Support\Facades\Mail;

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

        // Array untuk menyimpan usulan baru agar bisa dikirim summary ke admin di akhir
        $daftarUsulanBaru = [];

        // Penanda apakah saat ini adalah awal bulan triwulan
        $isAwalTriwulan = in_array(Carbon::now()->format('m-d'), ['01-01', '04-01', '07-01', '10-01']);
        $cacheKeyTriwulan = 'notif_triwulan_' . Carbon::now()->format('Y_m');

        // Big Data Optimized: Menggunakan chunkById(500) dan eager loading untuk menghindari N+1 Query Problem
        Pegawai::with(['riwayatAngkaKredit' => function ($query) {
            $query->orderBy('tmt_angka_kredit', 'desc');
        }])->chunkById(500, function ($pegawais) use ($bar, $matriksKamus, $leadCheckDays, $freqUploadDays, &$daftarUsulanBaru, $isAwalTriwulan, $cacheKeyTriwulan) {
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
                    $currentStatus = $existingTracker ? $existingTracker->status_saat_ini : null;
                    
                    if ($today->greaterThanOrEqualTo($nextKGB) && $isUploaded) {
                        $status = 'Aman';
                    } elseif ($currentStatus === 'Upload E-HRM' || $isConfirmed) {
                        $status = 'Upload E-HRM';
                        $keterangan = 'TTE Selesai. Menunggu upload SK E-HRM.';
                    } elseif ($currentStatus === 'Proses') {
                        $status = 'Proses';
                        $keterangan = 'Usulan sedang diproses (TTE) oleh admin.';
                    } elseif ($today->greaterThanOrEqualTo($startNotify)) {
                        $status = 'Usulan';
                        $keterangan = $today->greaterThanOrEqualTo($nextKGB) ? 'Masa KGB telah tiba. Segera ajukan usulan KGB.' : 'Segera ajukan usulan KGB.';
                    } else {
                        $status = 'Aman';
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
                            $daftarUsulanBaru[] = [
                                'nama' => $pegawai->nama,
                                'nip' => $pegawai->nip,
                                'kategori' => 'KGB'
                            ];
                            $tracker->update(['notified_at' => now()]);
                        } elseif ($status == 'Upload E-HRM') {
                            if ($isDueForUploadNotif) {
                                $notifiable = User::where('email', $pegawai->email)->first();
                                if (!$notifiable && $pegawai->email) {
                                    $notifiable = Notification::route('mail', $pegawai->email);
                                }
                                if ($notifiable) {
                                    $tmt = Carbon::parse($tracker->tanggal_target);
                                    $bulanTahun = $tmt->isoFormat('MMMM Y');
                                    
                                    $subject = '🔔 Notifikasi KGB: SK KGB Sudah Terbit';
                                    
                                    // Mengambil template dari DB, fallback ke text default jika tidak ada
                                    $ruleUpload = NotifikasiRules::where('kategori', 'KGB Upload Dokumen')->first();
                                    if ($ruleUpload) {
                                        $content = str_replace(
                                            ['{nama}', '{nip}', '{deadline}'],
                                            [$pegawai->nama, $pegawai->nip, $tmt->format('d-m-Y')],
                                            $ruleUpload->template_pesan
                                        );
                                    } else {
                                        $content = "Waktunya proses KGB untuk periode {$bulanTahun}.\n\nSegera lengkapi berkas dan upload SK Terakhir & SKP Anda ke sistem sekarang agar dapat diproses lebih lanjut oleh admin.";
                                    }
                                    
                                    $notifiable->notify(new SystemAlertNotification($pegawai, $subject, $content));
                                    
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

                            $currentAKStatus = $existingAK ? $existingAK->status_saat_ini : null;
                            $isConfirmedAK = $existingAK && $existingAK->dikonfirmasi_at;
                            $isUploadedAK = $existingAK && ($existingAK->dokumen_terupload >= $existingAK->dokumen_total);

                            $skipTrackerUpdate = false;

                            // Penentuan Status Tracker
                            if ($isUploadedAK) {
                                $statusAK = 'Aman';
                                $keteranganAK = ''; // Akan dihapus nanti
                            } elseif ($currentAKStatus === 'Upload E-HRM' || $isConfirmedAK) {
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
                                // Jika data AK tidak ada atau nilainya 0, maka belum memenuhi syarat untuk dihitung KP
                                $statusAK = 'Data Tidak Lengkap'; 
                                $keteranganAK = 'Peringatan: Data Riwayat AK tidak ditemukan di e-HRM atau bernilai 0. Segera upload/update SK PAK Anda.';
                            } else {
                                if ($kekuranganAK <= 0) {
                                    if ($isKenaikanJenjang) {
                                        $sudahLulus = $existingAK && str_contains($existingAK->keterangan, 'Lulus UKOM');
                                        if ($currentAKStatus === 'Usulan' && $sudahLulus) {
                                            $statusAK = 'Usulan';
                                            $keteranganAK = "Lulus UKOM. Segera usulkan {$namaProses} ke {$tujuanProses}";
                                        } else {
                                            $statusAK = 'Menunggu UKOM';
                                            $keteranganAK = "AK memenuhi target. Segera daftarkan Uji Kompetensi";
                                        }
                                    } else {
                                        $statusAK = 'Usulan';
                                        $keteranganAK = "AK memenuhi target. Segera usulkan {$namaProses} ke {$tujuanProses}";
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

                            // Tentukan tanggal target notifikasi (saat AK mencukupi / mendekati)
                            $targetDate = null;
                            if (in_array($statusAK, ['Usulan', 'Mendekati', 'Proses', 'Menunggu UKOM'])) {
                                if ($existingAK && $existingAK->tanggal_target) {
                                    $targetDate = $existingAK->tanggal_target;
                                } else {
                                    $targetDate = Carbon::now()->format('Y-m-d');
                                }
                            }

                            // Manajemen Database Tracker
                            if (!$skipTrackerUpdate) {
                                if (in_array($statusAK, ['Usulan', 'Mendekati', 'Proses', 'Menunggu UKOM'])) {
                                    $tracker = DashboardTracker::updateOrCreate(
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

                                    // Tambahkan ke Usulan Baru jika belum dinotifikasi
                                    if ($statusAK == 'Usulan' && !$tracker->notified_at) {
                                        $daftarUsulanBaru[] = [
                                            'nama' => $pegawai->nama,
                                            'nip' => $pegawai->nip,
                                            'kategori' => $kategoriSekarang
                                        ];
                                        $tracker->update(['notified_at' => now()]);
                                    }

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

                        // --- HITUNG TANGGAL TARGET (tmt_struktural + 1 tahun) ---
                        // tmt_struktural → fallback ke riwayat_jabatan terakhir
                        $tmtJabatan = $pegawai->tmt_struktural;
                        if (!$tmtJabatan && $pegawai->riwayat_jabatan) {
                            $latestJabatan = $pegawai->riwayat_jabatan->first();
                            if ($latestJabatan && $latestJabatan->tmt_jabatan) {
                                $tmtJabatan = $latestJabatan->tmt_jabatan;
                            }
                        }

                        $tanggalTargetStruktural = null;
                        if ($tmtJabatan) {
                            $tanggalTargetStruktural = Carbon::parse($tmtJabatan)->addYear(); // Jabatan Struktural: 1 Tahun
                        }

                        // --- FILTER H-2 BULAN (60 hari sebelum tanggal target 1 Tahun) ---
                        $startNotifyStruktural = $tanggalTargetStruktural
                            ? $tanggalTargetStruktural->copy()->subDays(60)
                            : null;

                        $inNotifyWindow = $startNotifyStruktural && $today->greaterThanOrEqualTo($startNotifyStruktural);

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
                        $isUploadedStruct = $existingKPStruct && ($existingKPStruct->dokumen_terupload >= $existingKPStruct->dokumen_total);

                        if ($isUploadedStruct) {
                            $statusStruktural = 'Aman';
                            $keteranganStruktural = '';
                        } elseif ($currentStructStatus === 'Upload E-HRM' || $isConfirmedStruct) {
                            $statusStruktural = 'Upload E-HRM';
                            $keteranganStruktural = 'TTE Selesai. Menunggu upload SK E-HRM.';
                        } elseif ($currentStructStatus === 'Proses') {
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

                        } elseif (!$tmtJabatan) {
                            // Data tmt jabatan (tmt pelantikan) tidak ada
                            $statusStruktural = 'Aman';
                            $keteranganStruktural = 'Data TMT Struktural / Pelantikan tidak tersedia';

                        } elseif ($pegawai->tmt_pangkat_terakhir && Carbon::parse($pegawai->tmt_pangkat_terakhir)->greaterThanOrEqualTo(Carbon::parse($tmtJabatan))) {
                            // Jika Pangkat Terakhir lebih baru ATAU sama dengan TMT Jabatan, artinya sudah pernah naik pangkat di jabatan ini.
                            // Atau SK Pangkat dan SK Jabatan terbit bersamaan (tmt sama).
                            // Struktural hanya bisa naik pangkat 1 kali dalam masa jabatannya.
                            $statusStruktural = 'Aman';
                            $keteranganStruktural = 'Sudah pernah menerima Kenaikan Pangkat dalam masa jabatan ini';

                        } elseif ($inNotifyWindow && $masaPangkat >= 1) { // Tetap butuh 1 tahun dalam pangkat terakhir
                            // Window H-2 bulan dari 1 Tahun Jabatan + syarat masa pangkat 1 tahun
                            $statusStruktural    = 'Usulan';
                            $keteranganStruktural = 'Memenuhi Syarat Kenaikan Pangkat Pilihan (Struktural 1 Tahun)';
                        }
                        if ($statusStruktural != 'Aman') {
                            $trackerStruct = DashboardTracker::updateOrCreate(
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
                            
                            // Tambahkan ke Usulan Baru jika belum dinotifikasi
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

                    // Cek history/status sebelumnya
                    $existingKPReguler = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                        ->where('kategori', 'KP_Reguler')
                        ->first();

                    $currentRegulerStatus = $existingKPReguler ? $existingKPReguler->status_saat_ini : null;
                    $isConfirmedReguler   = $existingKPReguler && $existingKPReguler->dikonfirmasi_at;
                    $isUploadedReguler    = $existingKPReguler && ($existingKPReguler->dokumen_terupload >= $existingKPReguler->dokumen_total);

                    if ($isUploadedReguler) {
                        $statusReguler = 'Aman';
                        $keteranganReguler = '';
                    } elseif ($currentRegulerStatus === 'Upload E-HRM' || $isConfirmedReguler) {
                        $statusReguler = 'Upload E-HRM';
                        $keteranganReguler = 'TTE Selesai. Menunggu upload SK E-HRM.';
                    } elseif ($currentRegulerStatus === 'Proses') {
                        $statusReguler    = 'Proses';
                        $keteranganReguler = "Sedang diproses admin ({$masaKerjaLabel})";
                    } elseif ($masaPangkatReguler >= 48) {
                        // Masa pangkat >= 4 tahun (48 bulan) → USULAN
                        $statusReguler    = 'Usulan';
                        $keteranganReguler = $masaKerjaLabel;
                    }

                    if ($statusReguler == 'Aman') {
                        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                            ->where('kategori', 'KP_Reguler')
                            ->delete();
                    } else {
                        $trackerReg = DashboardTracker::updateOrCreate(
                            [
                                'pegawai_id' => $pegawai->id_pegawai_api,
                                'kategori'   => 'KP_Reguler',
                            ],
                            [
                                'status_saat_ini' => $statusReguler,
                                'keterangan'      => $keteranganReguler,
                                'dokumen_total'   => 1, // atau sesuai kebutuhan reguler
                                'tanggal_target'  => $tanggalTargetReguler->format('Y-m-d'),
                            ]
                        );

                        // Tambahkan ke Usulan Baru jika belum dinotifikasi
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

                        $trackerHutang = DashboardTracker::updateOrCreate(
                            ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'DIKLAT_HUTANG'],
                            [
                                'status_saat_ini' => 'Upload E-HRM',
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

                        $trackerAnomali = DashboardTracker::updateOrCreate(
                            ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'DIKLAT_ANOMALI'],
                            [
                                'status_saat_ini' => 'Upload E-HRM',
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

        // --- KIRIM SUMMARY EMAIL KE ADMIN ---
        
        // Ambil semua data usulan yang berstatus 'Usulan' untuk PDF Detail dan Notifikasi Pegawai
        $trackersUsulan = DashboardTracker::with('pegawai')->where('status_saat_ini', 'Usulan')->get();
        
        $dbTotalUsulan = [];
        $detailUsulan = [];

        foreach ($trackersUsulan as $t) {
            $kat = $t->kategori;
            
            // Simpan Rekap Total per Kategori
            if (!isset($dbTotalUsulan[$kat])) {
                $dbTotalUsulan[$kat] = 0;
            }
            $dbTotalUsulan[$kat]++;

            // Simpan detail RINCI (Nama, NIP, Pangkat/Gol, Jabatan, TMT Target, Keterangan) untuk PDF
            $pegawaiInfo = $t->pegawai;
            if ($pegawaiInfo) {
                $tmtTarget = $t->tanggal_target 
                    ? Carbon::parse($t->tanggal_target)->format('d-m-Y') 
                    : '-';

                $detailUsulan[$kat][] = [
                    'nama'              => $pegawaiInfo->nama,
                    'nip'               => $pegawaiInfo->nip,
                    'pangkat_golongan'  => $pegawaiInfo->pangkat_golongan ?? '-',
                    'jabatan'           => $pegawaiInfo->jabatan_saat_ini ?? $pegawaiInfo->tipe_jabatan ?? '-',
                    'tmt_target'        => $tmtTarget,
                    'keterangan'        => $t->keterangan ?? '-',
                ];
            }
        }

        // Email tetap dikirim saat ada daftarUsulanBaru (walau mungkin semua langsung diproses) 
        // ATAU saat masih ada tumpukan Usulan di DB
        if (!empty($daftarUsulanBaru) || !empty($dbTotalUsulan)) {
            $admins = User::whereIn('role', ['super_admin', 'admin_pegawai'])->get();
            if ($admins->count() > 0) {
                $subject = "Daftar Usulan Tersedia Kepegawaian";
                $messageBody = "Berikut adalah ringkasan total usulan yang perlu diproses di sistem saat ini:\n\n";
                
                // Gunakan dummy pegawai untuk Manual Notification karena struktur Mail\ManualNotification mewajibkan satu pegawai.
                // Kita buat Dummy objek standar untuk fallback
                $dummyPegawai = new \stdClass();
                $dummyPegawai->nama = "Tim Kepegawaian";

                if (!empty($dbTotalUsulan)) {
                    foreach ($dbTotalUsulan as $kategori => $jumlah) {
                        $namaKategori = str_replace('_', ' ', $kategori);
                        $messageBody .= "• {$namaKategori}: {$jumlah} usulan\n";
                    }
                } else {
                    $messageBody .= "Saat ini tidak ada antrean Usulan.\n";
                }
                
                // Content khusus untuk Lonceng Database (Lebih ringkas)
                $dbContent = $messageBody;

                $messageBody .= "\nSilakan cek tabel daftar usulan untuk memproses verifikasi dokumen ini secara kolektif.";

                // Siapkan data untuk PDF (hanya untuk attachment email, TIDAK simpan file ke disk)
                $pdfData = [
                    'summary' => $dbTotalUsulan,
                    'new_usulan' => $daftarUsulanBaru,
                    'details' => $detailUsulan
                ];

                foreach ($admins as $admin) {
                     if ($admin->email) {
                         try {
                              $admin->notify(new SystemAlertNotification($dummyPegawai, $subject, $messageBody, null, $pdfData));
                         } catch (\Exception $e) {
                              \Log::error("Gagal mengirim notifikasi rekap usulan ke Admin {$admin->email}: " . $e->getMessage());
                         }
                     }
                }
                ActivityLogger::logSystem("Mengirim notifikasi rekap usulan baru ke admin (" . count($daftarUsulanBaru) . " usulan)");

                // --- KIRIM EMAIL NOTIFIKASI KE MASING-MASING PEGAWAI ---
                // Ekstrak pegawai unik dari koleksi $trackersUsulan
                $notifiedEmployees = [];

                foreach ($trackersUsulan as $tracker) {
                    $pegawai = $tracker->pegawai;
                    
                    // Pastikan pegawai ada, punya email, dan belum dikirimi email pada iterasi ini
                    if ($pegawai && $pegawai->email && !in_array($pegawai->id_pegawai_api, $notifiedEmployees)) {
                        
                        $namaKategori = str_replace('_', ' ', $tracker->kategori);
                        $empSubject = "Pemberitahuan Usulan " . $namaKategori;
                        
                        $empMessage = "";
                        $rule = NotifikasiRules::where('kategori', $tracker->kategori)->first();

                        if ($rule) {
                            $empMessage = str_replace(
                                ['{nama}', '{nip}', '{kategori}'],
                                [$pegawai->nama, $pegawai->nip, $namaKategori],
                                $rule->template_pesan
                            );
                        } else {
                            // Fallback jika rule terhapus dari DB
                            switch ($tracker->kategori) {
                                case 'KGB':
                                    $empMessage = "Anda telah mendekati jadwal Kenaikan Gaji Berkala (KGB). Status KGB Anda saat ini adalah 'Usulan'.\n\nMohon segera mempersiapkan berkas administrasi dan melengkapinya agar dapat diproses oleh Admin Kepegawaian.";
                                    break;
                                case 'KP_Reguler':
                                case 'KP_Struktural':
                                    $empMessage = "Masa pangkat Anda telah memenuhi syarat Kenaikan Pangkat (KP). Status KP Anda saat ini adalah 'Usulan'.\n\nMohon segera mempersiapkan berkas administrasi dan melengkapinya agar dapat diproses oleh Admin Kepegawaian.";
                                    break;
                                case 'DIKLAT_HUTANG':
                                case 'DIKLAT_ANOMALI':
                                    $empMessage = "Terdapat kewajiban Diklat yang perlu Anda selesaikan. Status Diklat Anda saat ini adalah 'Usulan'.\n\nMohon segera mempersiapkan berkas administrasi dan melengkapinya agar dapat diproses oleh Admin Kepegawaian.";
                                    break;
                                default:
                                    // Fallback for KJ or other categories like the user's example
                                    $empMessage = "Angka Kredit / Syarat Anda telah mencukupi. Status {$namaKategori} Anda saat ini adalah 'Usulan'.\n\nMohon segera mempersiapkan berkas administrasi dan melengkapinya agar dapat diproses oleh Admin Kepegawaian.";
                                    break;
                            }
                        }

                        try {
                            // Kirim alert lengkap (Email Biru + Notif Lonceng Database) tanpa PDF
                            $notifiable = User::where('email', $pegawai->email)->first();
                            if (!$notifiable) {
                                $notifiable = Notification::route('mail', $pegawai->email);
                            }
                            
                            $notifiable->notify(new SystemAlertNotification($pegawai, $empSubject, $empMessage));
                            
                            // Catat ID pegawai agar tidak dikirim email dobel jika dia punya 2 usulan berbeda
                            $notifiedEmployees[] = $pegawai->id_pegawai_api;
                        } catch (\Exception $e) {
                            \Log::error("Gagal mengirim notifikasi personal ke Pegawai {$pegawai->email}: " . $e->getMessage());
                        }
                    }
                }

                if (count($notifiedEmployees) > 0) {
                    ActivityLogger::logSystem("Mengirim notifikasi personal ke " . count($notifiedEmployees) . " pegawai terkait usulannya.");
                }
            }
        }

        $this->info('✅ Tracker update & Notifikasi terkirim!');
        ActivityLogger::logSystem('Perhitungan tracker selesai untuk ' . $count . ' pegawai');
    }
}