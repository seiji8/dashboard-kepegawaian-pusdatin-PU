<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\User; // Butuh User untuk kirim notif
use Illuminate\Support\Facades\Notification;
use App\Notifications\KgbMendekatiNotification;
use App\Notifications\KgbUsulanNotification;

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
                $startNotify = $nextKGB->copy()->subMonths(2); // H-2 Bulan

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

                    // 3. LOGIKA KIRIM NOTIFIKASI (Cek kolom notified_at)
                    if (!$tracker->notified_at) {
                        
                        // KASUS A: MENDEKATI -> KIRIM KE ADMIN
                        if ($status == 'Mendekati') {
                            $admins = User::whereIn('role', ['super_admin', 'admin_pegawai'])->get();
                            if ($admins->count() > 0) {
                                Notification::send($admins, new KgbMendekatiNotification($pegawai));
                                $tracker->update(['notified_at' => now()]);
                                ActivityLogger::logSystem("Mengirim notifikasi KGB Mendekati ke admin untuk pegawai {$pegawai->nama}", $pegawai->nip);
                            }
                        }

                        // KASUS B: USULAN -> KIRIM KE PEGAWAI (EMAIL & DB)
                        elseif ($status == 'Usulan') {
                            // 1. Coba cari User yang punya email ini (agar bisa notif ke DB Dashboard juga)
                            $notifiable = User::where('email', $pegawai->email)->first();
                            
                            // 2. Jika tidak punya akun User, kirim langusung ke Email Pegawai
                            if (!$notifiable && $pegawai->email) {
                                $notifiable = Notification::route('mail', $pegawai->email);
                            }

                            if ($notifiable) {
                                $notifiable->notify(new KgbUsulanNotification($tracker));
                                $tracker->update(['notified_at' => now()]);
                                ActivityLogger::logSystem("Mengirim notifikasi KGB Usulan ke pegawai {$pegawai->nama} ({$pegawai->email})", $pegawai->nip);
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