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

                // Tentukan Status
                if ($today->greaterThanOrEqualTo($nextKGB)) {
                    $status = 'Usulan';
                    $keterangan = 'Waktunya Pengajuan! Segera Upload Dokumen.';
                } 
                elseif ($today->greaterThanOrEqualTo($startNotify)) {
                    $status = 'Mendekati';
                    $keterangan = 'KGB akan segera tiba dalam waktu dekat.';
                }

                // ==========================================
                // UPDATE DATABASE & NOTIFIKASI
                // ==========================================
                
                if ($status != 'Aman') {
                    // 1. Simpan/Update Tracker (Pakai updateOrCreate biar history notif terjaga)
                    $tracker = DashboardTracker::updateOrCreate(
                        [
                            'pegawai_id' => $pegawai->id_pegawai_api, // Gunakan ID Pegawai sebagai kunci
                            'kategori'   => 'KGB',
                        ],
                        [
                            'tanggal_target'    => $nextKGB->format('Y-m-d'),
                            'status_saat_ini'   => $status,
                            'keterangan'        => $keterangan,
                            'dokumen_total'     => 1,
                            // 'dokumen_terupload' => 0 // Jangan reset ini kalau update!
                        ]
                    );

                    // 2. Cek Perubahan Status (Reset Notifikasi jika status berubah)
                    // Misal: Dari 'Mendekati' berubah jadi 'Usulan', kita harus kirim notif baru.
                    if ($tracker->wasChanged('status_saat_ini')) {
                        $tracker->update(['notified_at' => null]); // Reset agar dikirim ulang
                    }

                    // 3. LOGIKA KIRIM NOTIFIKASI
                    
                    // Cek kapan terakhir dikirim
                    $lastNotifDate = $tracker->notified_at ? Carbon::parse($tracker->notified_at) : null;
                    
                    // Hitung selisih hari dari terakhir kirim sampai hari ini
                    // Jika belum pernah dikirim, anggap sudah memenuhi syarat (diff = infinite/sufficient)
                    $daysSinceLast = $lastNotifDate ? $lastNotifDate->diffInDays($today) : 9999;
                    
                    // Cek apakah hari ini "Jadwalnya Kirim" berdasarkan frekuensi ($freqUploadDays)
                    // Logic: Jika selisih hari >= frekuensi, maka kirim.
                    // Contoh: Freq 2 hari. Last: Kemarin (1 hari lalu). 1 >= 2 False.
                    // Last: 2 hari lalu. 2 >= 2 True.
                    // Khusus Freq 1 hari: Pastikan tidak kirim 2x di hari yang sama.
                    $isDueForUploadNotif = ($daysSinceLast >= $freqUploadDays);
                    
                    // Pastikan tidak double kirim di hari yang sama (meski freq=1)
                    if ($lastNotifDate && $lastNotifDate->isToday()) {
                        $isDueForUploadNotif = false;
                    }


                    // KASUS A: MENDEKATI -> KIRIM KE ADMIN (Cukup Sekali Saja)
                    if ($status == 'Mendekati' && !$tracker->notified_at) {
                        $admins = User::whereIn('role', ['super_admin', 'admin_pegawai'])->get();
                        if ($admins->count() > 0) {
                            Notification::send($admins, new KgbMendekatiNotification($pegawai));
                            $tracker->update(['notified_at' => now()]);
                            ActivityLogger::logSystem("Mengirim notifikasi KGB Mendekati ke admin untuk pegawai {$pegawai->nama}", $pegawai->nip);
                        }
                    }

                    // KASUS B: USULAN -> KIRIM KE PEGAWAI (Sesuai Frekuensi)
                    elseif ($status == 'Usulan') {
                        // Cek kelengkapan dokumen
                        $dokumenLengkap = $tracker->dokumen_terupload >= $tracker->dokumen_total;

                        // Jika BELUM lengkap DAN Masuk Jadwal Kirim -> Kirim Notif
                        if (!$dokumenLengkap && $isDueForUploadNotif) {
                            
                            // 1. Coba cari User yang punya email ini (agar bisa notif ke DB Dashboard juga)
                            $notifiable = User::where('email', $pegawai->email)->first();
                            
                            // 2. Jika tidak punya akun User, kirim langusung ke Email Pegawai
                            if (!$notifiable && $pegawai->email) {
                                $notifiable = Notification::route('mail', $pegawai->email);
                            }

                            if ($notifiable) {
                                $notifiable->notify(new KgbUsulanNotification($tracker));
                                $tracker->update(['notified_at' => now()]); // Update waktu notif terakhir
                                ActivityLogger::logSystem("Mengirim notifikasi Berkala (Setiap {$freqUploadDays} hari) KGB Usulan ke pegawai {$pegawai->nama}", $pegawai->nip);
                            }
                        }
                    }

                } else {
                    // Jika status 'Aman', HAPUS data di tracker agar dashboard bersih
                    DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                    ->where('kategori', 'KGB')
                                    ->delete();
                }
            } 
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Tracker update & Notifikasi terkirim!');
        ActivityLogger::logSystem('Perhitungan tracker selesai untuk ' . $pegawais->count() . ' pegawai');
    }
}