<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Log;

class TubelService implements TrackerInterface
{
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        // Skip dummy/test data as they don't need Tubel calculation
        if (str_contains(strtolower($pegawai->id_pegawai_api), 'dummy') || 
            str_contains(strtolower($pegawai->nip), 'dummy')) {
            return;
        }

        $riwayatTubel = \App\Models\RiwayatTubel::where('nip', $pegawai->nip)->get();

        // Cari tubel yang masih aktif: tanggal_mulai ada dan belum selesai
        $tubelAktif = $riwayatTubel->first(function ($t) use ($today) {
            if (!$t->tanggal_mulai) return false;
            if ($today->lt(Carbon::parse($t->tanggal_mulai))) return false;
            
            $selesai = $t->perpanjangan2_tanggal_mulai
                ?? $t->perpanjangan1_tanggal_mulai
                ?? $t->tanggal_selesai;
                
            if ($selesai && $today->gt($selesai)) return false;
            return true;
        });

        if ($tubelAktif) {
            $selesaiEfektif = $tubelAktif->perpanjangan2_tanggal_mulai
                ?? $tubelAktif->perpanjangan1_tanggal_mulai
                ?? $tubelAktif->tanggal_selesai;

            $statusTubel    = 'Sedang Tubel';
            $keteranganTubel = 'Sedang menjalani Tugas Belajar';

            if ($selesaiEfektif) {
                $hariSisa = $today->diffInDays($selesaiEfektif, false);
                if ($hariSisa <= 60 && $hariSisa >= 0) {
                    $statusTubel    = 'Proses Pengaktifan';
                    $keteranganTubel = "Sisa {$hariSisa} hari menuju selesai Tubel. Segera siapkan surat pengaktifan kembali.";
                }
                $keteranganTubel .= " | Selesai: " . $selesaiEfektif->format('d-m-Y');
            } else {
                $keteranganTubel .= ' (tanggal selesai belum ditetapkan)';
            }

            $existingTubel = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                ->where('kategori', 'TUBEL')->first();

            $trackerTubel = DashboardTracker::updateOrCreate(
                ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'TUBEL'],
                [
                    'status_saat_ini' => $statusTubel,
                    'keterangan'      => $keteranganTubel,
                    'dokumen_total'   => 3,
                    'tanggal_target'  => $selesaiEfektif ? $selesaiEfektif->format('Y-m-d') : null,
                ]
            );

            // Tambahkan kelengkapan dokumen untuk TUBEL jika belum ada
            if ($trackerTubel->wasRecentlyCreated || \App\Models\KelengkapanDokumen::where('dashboard_tracker_id', $trackerTubel->id)->count() === 0) {
                $dokumenTubel = [
                    'Surat Pengantar Unit Kerja',
                    'SK Tugas Belajar',
                    'Ijazah / Transkrip Nilai'
                ];
                foreach ($dokumenTubel as $dok) {
                    \App\Models\KelengkapanDokumen::firstOrCreate([
                        'dashboard_tracker_id' => $trackerTubel->id,
                        'nama_dokumen'         => $dok,
                        'nip'                  => $pegawai->nip
                    ]);
                }
            }

            if ($statusTubel === 'Proses Pengaktifan' && $trackerTubel->wasChanged('status_saat_ini')) {
                $admins = User::whereIn('role', ['super_admin', 'admin_pegawai'])->get();
                foreach ($admins as $admin) {
                    if ($admin->email) {
                        try {
                            $subjekAdmin = "🎓 Persiapan Pengaktifan Tubel: {$pegawai->nama}";
                            $pesanAdmin  = "Yth. Admin Kepegawaian,\n\n"
                                . "Pegawai berikut akan segera menyelesaikan Tugas Belajar (Tubel) dan perlu disiapkan surat pengaktifan kembali:\n\n"
                                . "Nama    : {$pegawai->nama}\n"
                                . "NIP     : {$pegawai->nip}\n"
                                . "Keterangan: {$keteranganTubel}\n\n"
                                . "Mohon segera siapkan surat pengaktifan kembali agar pegawai dapat aktif bekerja setelah selesai tubel.\n\n"
                                . "Terima kasih.";
                            $admin->notify(new SystemAlertNotification((object)['nama' => 'Tim Kepegawaian'], $subjekAdmin, $pesanAdmin));
                        } catch (\Exception $e) {
                            Log::error("Gagal kirim notif Tubel ke admin {$admin->email}: " . $e->getMessage());
                        }
                    }
                }
                ActivityLogger::logSystem("Mengirim notifikasi Proses Pengaktifan Tubel untuk {$pegawai->nama}", $pegawai->nip);
            }
        } else {
            DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                ->where('kategori', 'TUBEL')->delete();
        }
    }
}
