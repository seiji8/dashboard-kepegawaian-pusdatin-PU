<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use App\Helpers\ActivityLogger;
use Carbon\Carbon;

class DiklatService implements TrackerInterface
{
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        if (str_contains(strtolower($pegawai->id_pegawai_api ?? ''), 'dummy') || 
            str_contains(strtolower($pegawai->nip ?? ''), 'dummy')) {
            return;
        }

        $riwayatDiklat = \App\Models\RiwayatDiklat::where('nip', $pegawai->nip)->get();

        if ($riwayatDiklat->isNotEmpty()) {
            $belumUploadDiklat = $riwayatDiklat->filter(function ($d) use ($today) {
                return ($d->status_diklat == 0 && $d->tanggal_selesai && $today->greaterThan(Carbon::parse($d->tanggal_selesai)))
                    || ($d->status_diklat == 1 && empty($d->file_sertifikat) && empty($d->arsip));
            });

            if ($belumUploadDiklat->isNotEmpty()) {
                $jumlahBelumUpload = $belumUploadDiklat->count();
                $namaDiklatList = $belumUploadDiklat->pluck('nama_diklat')->toArray();
                $keterangan = $jumlahBelumUpload == 1
                    ? 'Sertifikat diklat belum diupload ke E-HRM'
                    : $jumlahBelumUpload . ' sertifikat diklat belum diupload ke E-HRM';

                $trackerBelumUpload = DashboardTracker::updateOrCreate(
                    ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'DIKLAT_BELUM_UPLOAD'],
                    [
                        'status_saat_ini' => 'Upload E-HRM',
                        'keterangan' => $keterangan,
                        'dokumen_total' => $jumlahBelumUpload,
                        'dokumen_terupload' => 0,
                        'tanggal_target' => $belumUploadDiklat->first()->tanggal_selesai ? Carbon::parse($belumUploadDiklat->sortBy('tanggal_selesai')->first()->tanggal_selesai)->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                    ]
                );

                $this->sendUploadEhrmNotification($pegawai, 'DIKLAT_BELUM_UPLOAD', $trackerBelumUpload, $namaDiklatList);
            } else {
                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                    ->where('kategori', 'DIKLAT_BELUM_UPLOAD')->delete();
            }
        } else {
            DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                ->where('kategori', 'DIKLAT_BELUM_UPLOAD')->delete();
        }
    }

    private function sendUploadEhrmNotification(Pegawai $pegawai, string $kategori, DashboardTracker $tracker, array $namaDiklatList): void
    {
        $notifCacheKey = 'upload_ehrm_notif_' . $pegawai->id_pegawai_api . '_' . $kategori;
        if (!Cache::has($notifCacheKey)) {
            $rule = \App\Models\NotifikasiRules::where('kategori', 'DIKLAT Upload Dokumen')->first();
            if ($rule) {
                $notifiable = User::where('email', $pegawai->email)->first();
                if (!$notifiable && $pegawai->email) {
                    $notifiable = Notification::route('mail', $pegawai->email);
                }
                if ($notifiable) {
                    $missingDocs = [];
                    foreach ($namaDiklatList as $diklat) {
                        $missingDocs[] = "- Sertifikat Diklat: " . $diklat;
                    }

                    if (empty($missingDocs)) return;

                    $missingStr = implode("\n", $missingDocs);
                    $pesan = str_replace(
                        ['{nama}', '{missing_documents}'],
                        [$pegawai->nama, $missingStr],
                        $rule->template_pesan
                    );

                    try {
                        $notifiable->notify(new SystemAlertNotification($pegawai, "📋 Permintaan Upload Sertifikat Diklat", $pesan));
                        Cache::put($notifCacheKey, true, now()->addDays(1));
                        ActivityLogger::logSystem("Mengirim notifikasi Upload Sertifikat Diklat ke pegawai {$pegawai->nama}", $pegawai->nip);
                    } catch (\Exception $e) {}
                }
            }
        }
    }
}