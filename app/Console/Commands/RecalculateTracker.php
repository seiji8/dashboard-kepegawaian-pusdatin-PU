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
                        $targetAK = $matriks->target_ak;
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
                                ->where('kategori', $kategoriSekarang)
                                ->first();

                            $isProses = $existingAK && ($existingAK->status_saat_ini === 'Proses' || $existingAK->dikonfirmasi_at);

                            // Penentuan Status Tracker
                            if ($isProses) {
                                $statusAK = 'Proses';
                                $keteranganAK = 'Sedang diproses admin';
                            } elseif (is_null($latestAK)) {
                                $statusAK = 'Usulan'; // Dibuat mode usulan agar menonjol di dashboard (urgent)
                                $keteranganAK = 'Peringatan: Data Riwayat AK tidak ditemukan di e-HRM. Segera upload/update data Anda.';
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

                            // Manajemen Database Tracker
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
                                    ]
                                );

                                // Hapus Kategori Lawan
                                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                    ->where('kategori', $kategoriLawan)
                                    ->delete();
                            } else {
                                // Status Aman -> Hapus keduanya
                                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                    ->whereIn('kategori', ['KP_Jafung', 'KJ_Jafung'])
                                    ->delete();
                            }
                        }
                    }
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