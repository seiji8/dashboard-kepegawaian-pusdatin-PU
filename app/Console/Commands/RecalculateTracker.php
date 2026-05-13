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

        // Bersihkan semua tracker ber-status 'Mendekati' yang mungkin masih tersisa
        // (Status ini tidak lagi disimpan ke DB — hanya kirim notifikasi ke pegawai)
        $deletedMendekati = DashboardTracker::where('status_saat_ini', 'Mendekati')->delete();
        if ($deletedMendekati > 0) {
            $this->info("🧹 Membersihkan {$deletedMendekati} tracker lama ber-status 'Mendekati'...");
        }
        
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

        $context = [
            'leadCheckDays' => $leadCheckDays,
            'freqUploadDays' => $freqUploadDays,
            'force' => $this->option('force'),
            'matriksKamus' => $matriksKamus,
        ];

        // Big Data Optimized: Menggunakan chunkById(500) dan eager loading untuk menghindari N+1 Query Problem
        Pegawai::with(['riwayatAngkaKredit' => function ($query) {
            $query->orderBy('tmt_angka_kredit', 'desc');
        }, 'riwayat_jabatan' => function ($query) {
            $query->orderBy('tmt_jabatan', 'desc');
        }])->chunkById(500, function ($pegawais) use ($bar, $context, &$daftarUsulanBaru) {
            $kgbService = new \App\Services\Tracker\KgbTrackerService();
            $kpService = new \App\Services\Tracker\KenaikanPangkatService();
            $kjService = new \App\Services\Tracker\KenaikanJenjangService();
            $tubelService = new \App\Services\Tracker\TubelService();
            $diklatService = new \App\Services\Tracker\DiklatService();

            foreach ($pegawais as $pegawai) {
                $today = Carbon::now();

                $kgbService->process($pegawai, $today, $daftarUsulanBaru, $context);
                $kpService->process($pegawai, $today, $daftarUsulanBaru, $context);
                $kjService->process($pegawai, $today, $daftarUsulanBaru, $context);
                $tubelService->process($pegawai, $today, $daftarUsulanBaru, $context);
                $diklatService->process($pegawai, $today, $daftarUsulanBaru, $context);

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