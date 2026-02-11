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

class RecalculateTracker extends Command
{
    protected $signature = 'tracker:run';
    protected $description = 'Hitung ulang status KGB dan Pangkat serta Kirim Notifikasi';

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

        // --- PERUBAHAN 1: HAPUS TRUNCATE ---
        // Kita JANGAN kosongkan tabel, supaya history 'notified_at' tidak hilang.
        // Schema::disableForeignKeyConstraints();
        // DashboardTracker::truncate(); 
        // Schema::enableForeignKeyConstraints();

        $pegawais = Pegawai::all();
        $bar = $this->output->createProgressBar(count($pegawais));
        $bar->start();

        foreach ($pegawais as $pegawai) {
                        // ==========================================
                // LOGIKA KGB
                // ==========================================
                if ($pegawai->tmt_kgb_terakhir) {
                    // Rumus: TMT Terakhir + 2 Tahun
                    $nextKGB = Carbon::parse($pegawai->tmt_kgb_terakhir)->addYears(2);
                    $today = Carbon::now();
                    // Gunakan interval dari DB untuk menentukan kapan mulai notif (Lead Time)
                    $startNotify = $nextKGB->copy()->subDays($leadCheckDays);

                    $status = 'Aman';
                    $keterangan = 'Masih dalam masa aman';

                    // Ambil data tracker lama untuk cek konfirmasi & upload
                    $existingTracker = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                        ->where('kategori', 'KGB')
                                        ->first();
                    
                    $isConfirmed = $existingTracker && $existingTracker->dikonfirmasi_at;
                    $isUploaded  = $existingTracker && ($existingTracker->dokumen_terupload >= $existingTracker->dokumen_total);

                    // LOGIKA BARU SESUAI REQUEST:
                    
                    // 1. Cek jika sudah lewat TMT (H-Day ke atas)
                    if ($today->greaterThanOrEqualTo($nextKGB)) {
                        if ($isUploaded) {
                            // Jika sudah upload, selesai (hilang dari dashboard)
                            $status = 'Aman';
                        } else {
                            // Masuk fase Upload E-HRM
                            $status = 'Upload E-HRM';
                            $keterangan = 'SK KGB sudah terbit? Silakan upload E-HRM.';
                        }
                    }
                    // 2. Cek jika masuk periode H-2 Bulan (Lead Time)
                    elseif ($today->greaterThanOrEqualTo($startNotify)) {
                        if ($isConfirmed) {
                            // Jika sudah dikonfirmasi admin -> PROSES
                            $status = 'Proses';
                            $keterangan = 'Usulan sedang diproses oleh admin.';
                        } else {
                            // Default: USULAN
                            $status = 'Usulan';
                            $keterangan = 'Segera ajukan usulan KGB.';
                        }
                    }

                    // ==========================================
                    // UPDATE DATABASE & NOTIFIKASI
                    // ==========================================
                    
                    if ($status != 'Aman') {
                        // 1. Simpan/Update Tracker
                        $tracker = DashboardTracker::updateOrCreate(
                            [
                                'pegawai_id' => $pegawai->id_pegawai_api, 
                                'kategori'   => 'KGB',
                            ],
                            [
                                'tanggal_target'    => $nextKGB->format('Y-m-d'),
                                'status_saat_ini'   => $status,
                                'keterangan'        => $keterangan,
                                'dokumen_total'     => 1,
                            ]
                        );

                        // 2. Cek Perubahan Status (Reset Notifikasi jika status berubah)
                        if ($tracker->wasChanged('status_saat_ini')) {
                            // Jangan reset notif jika berubah ke Proses, karena user sedang menunggu.
                            // Reset jika berubah ke Upload E-HRM agar notif upload jalan.
                            if ($status == 'Upload E-HRM' || $status == 'Usulan') {
                                $tracker->update(['notified_at' => null]);
                            }
                        }

                        // 3. LOGIKA KIRIM NOTIFIKASI
                        
                        $lastNotifDate = $tracker->notified_at ? Carbon::parse($tracker->notified_at) : null;
                        $daysSinceLast = $lastNotifDate ? $lastNotifDate->diffInDays($today) : 9999;
                        $isDueForUploadNotif = ($daysSinceLast >= $freqUploadDays);
                        
                        if ($lastNotifDate && $lastNotifDate->isToday()) {
                            $isDueForUploadNotif = false;
                        }

                        // KASUS A: USULAN (Merah) -> KIRIM KE ADMIN
                        if ($status == 'Usulan' && !$tracker->notified_at) {
                            $admins = User::whereIn('role', ['super_admin', 'admin_pegawai'])->get();
                            if ($admins->count() > 0) {
                                Notification::send($admins, new KgbMendekatiNotification($pegawai));
                                $tracker->update(['notified_at' => now()]);
                                ActivityLogger::logSystem("Mengirim notifikasi KGB Usulan ke admin untuk pegawai {$pegawai->nama}", $pegawai->nip);
                            }
                        }

                        // KASUS B: UPLOAD E-HRM (Hijau) -> KIRIM KE PEGAWAI
                        elseif ($status == 'Upload E-HRM') {
                            // Kirim notifikasi upload jika belum lengkap & sesuai jadwal
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
                        // Jika status 'Aman', HAPUS data di tracker
                        DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                        ->where('kategori', 'KGB')
                                        ->delete();
                    }          } 
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Tracker update & Notifikasi terkirim!');
        ActivityLogger::logSystem('Perhitungan tracker selesai untuk ' . $pegawais->count() . ' pegawai');
    }
}