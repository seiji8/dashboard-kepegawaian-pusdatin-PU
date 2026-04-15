<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\NotifikasiRules;
use App\Notifications\SystemAlertNotification;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\AnonymousNotifiable;

class SendPeriodicNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:periodic {--force-date= : YYYY-MM-DD format untuk memaksa jadwal (Testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic periodic notifications (Triwulan & Tahunan) to eligible employees.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $forceDate = $this->option('force-date');
        $now = $forceDate ? Carbon::parse($forceDate) : Carbon::now();

        $this->info("Memulai pengecekan Notifikasi Periodik pada " . $now->format('d-m-Y H:i:s'));

        // Cek Rule Notifikasi Tahunan
        // Syarat: Tanggal 1 Januari
        if ($now->month == 1 && $now->day == 1) {
            $this->processTahunan($now);
        } else {
            $this->info("Hari ini bukan 1 Januari. Lewati notifikasi tahunan.");
        }

        // Cek Rule Notifikasi Triwulan
        // Syarat: Tanggal 1 pada bulan Januari, April, Juli, Oktober
        if (in_array($now->month, [1, 4, 7, 10]) && $now->day == 1) {
            $this->processTriwulan($now);
        } else {
            $this->info("Hari ini bukan awal triwulan (1 Jan/Apr/Jul/Okt). Lewati notifikasi triwulan.");
        }

        $this->info("Selesai melaksanakan tugas periodik.");
        return self::SUCCESS;
    }

    /**
     * Ambil pegawai yang tipe_jabatan-nya Fungsional atau Struktural
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Pegawai>
     */
    private function getEligiblePegawai()
    {
        // POINT 4: Normalisasi Jabatan
        // Match kedua format: "Fungsional" (manual) dan "JABATAN FUNGSIONAL" (API E-HRM)
        return Pegawai::where(function ($q) {
            $q->whereIn('tipe_jabatan', ['Fungsional', 'Struktural', 'Jafung', 'JABATAN FUNGSIONAL', 'JABATAN STRUKTURAL']);
        })->get();
    }

    /**
     * Kirim notifikasi ke pegawai.
     * Prioritas 1: Cari User terkait (via username=NIP) → kirim via Notifiable (email + database)
     * Prioritas 2: Jika tidak ada User tapi ada email pegawai → kirim via AnonymousNotifiable (email only)
     */
    private function sendNotifToPegawai(Pegawai $pegawai, string $subject, string $content): string
    {
        // Coba cari User account untuk pegawai ini
        $user = User::where('username', $pegawai->nip)->first();

        if ($user) {
            $user->notify(new SystemAlertNotification($pegawai, $subject, $content));
            return 'user';
        }

        // Fallback: kirim via email langsung jika pegawai punya email
        if ($pegawai->email) {
            \Illuminate\Support\Facades\Notification::route('mail', $pegawai->email)
                ->notify(new SystemAlertNotification($pegawai, $subject, $content));
            return 'email';
        }

        return 'skip';
    }

    private function processTahunan(Carbon $now)
    {
        // FIX: kolom yang ada di notifikasi_rules adalah 'kategori', bukan 'nama_notifikasi'
        $rule = NotifikasiRules::where('kategori', 'Notifikasi Tahunan')->first();
        if (!$rule || !$rule->is_active) {
            $this->warn("Rule 'Notifikasi Tahunan' tidak ada atau sedang dinonaktifkan.");
            return;
        }

        $this->info("Menjalankan notifikasi Tahunan...");
        $pegawais = $this->getEligiblePegawai();
        $this->info("Ditemukan {$pegawais->count()} pegawai eligible (Fungsional/Struktural).");
        
        $countUser = 0;
        $countEmail = 0;
        $skipped = 0;

        foreach ($pegawais as $pegawai) {
            // Replacements
            $content = str_replace('{nip}', $pegawai->nip ?? '-', $rule->template_pesan);
            $content = str_replace('{tahun}', $now->year, $content);

            $result = $this->sendNotifToPegawai($pegawai, $rule->kategori, $content);

            if ($result === 'user') {
                $this->line("  ✓ [USER] Notif Tahunan → {$pegawai->nama} ({$pegawai->nip})");
                $countUser++;
            } elseif ($result === 'email') {
                $this->line("  ✓ [EMAIL] Notif Tahunan → {$pegawai->nama} ({$pegawai->email})");
                $countEmail++;
            } else {
                $this->line("  ✗ [SKIP] {$pegawai->nama} — tidak ada user atau email.");
                $skipped++;
            }
        }

        $total = $countUser + $countEmail;
        $this->info("Berhasil mengirim notifikasi Tahunan ke {$total} pegawai ({$countUser} via user, {$countEmail} via email langsung). {$skipped} dilewati.");
        ActivityLogger::logSystem("Mengirim notifikasi Tahunan ke {$total} pegawai Fungsional/Struktural.");
    }

    private function processTriwulan(Carbon $now)
    {
        // FIX: kolom yang ada di notifikasi_rules adalah 'kategori', bukan 'nama_notifikasi'
        $rule = NotifikasiRules::where('kategori', 'Notifikasi Triwulan')->first();
        if (!$rule || !$rule->is_active) {
            $this->warn("Rule 'Notifikasi Triwulan' tidak ada atau sedang dinonaktifkan.");
            return;
        }

        $this->info("Menjalankan notifikasi Triwulan...");
        
        // Deadline = Hari terakhir bulan ini
        $deadline = $now->copy()->endOfMonth()->isoFormat('D MMMM Y');

        $pegawais = $this->getEligiblePegawai();
        $this->info("Ditemukan {$pegawais->count()} pegawai eligible (Fungsional/Struktural).");
        
        $countUser = 0;
        $countEmail = 0;
        $skipped = 0;

        foreach ($pegawais as $pegawai) {
            // Replacements
            $content = str_replace('{nip}', $pegawai->nip ?? '-', $rule->template_pesan);
            $content = str_replace('{deadline}', $deadline, $content);

            $result = $this->sendNotifToPegawai($pegawai, $rule->kategori, $content);

            if ($result === 'user') {
                $this->line("  ✓ [USER] Notif Triwulan → {$pegawai->nama} ({$pegawai->nip})");
                $countUser++;
            } elseif ($result === 'email') {
                $this->line("  ✓ [EMAIL] Notif Triwulan → {$pegawai->nama} ({$pegawai->email})");
                $countEmail++;
            } else {
                $this->line("  ✗ [SKIP] {$pegawai->nama} — tidak ada user atau email.");
                $skipped++;
            }
        }

        $total = $countUser + $countEmail;
        $this->info("Berhasil mengirim notifikasi Triwulan ke {$total} pegawai ({$countUser} via user, {$countEmail} via email langsung). {$skipped} dilewati.");
        ActivityLogger::logSystem("Mengirim notifikasi Triwulan ke {$total} pegawai Fungsional/Struktural.");
    }
}
