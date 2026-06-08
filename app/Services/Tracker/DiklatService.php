<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use App\Models\DashboardTracker;
use Carbon\Carbon;

class DiklatService implements TrackerInterface
{
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        // Skip dummy/test data as they don't need Diklat calculation
        if (str_contains(strtolower($pegawai->id_pegawai_api ?? ''), 'dummy') || 
            str_contains(strtolower($pegawai->nip ?? ''), 'dummy')) {
            return;
        }

        $riwayatDiklat = \App\Models\RiwayatDiklat::where('nip', $pegawai->nip)->get();

        if ($riwayatDiklat->isNotEmpty()) {
            // --- BELUM UPLOAD SERTIFIKAT: status_diklat=0 AND tanggal_selesai sudah lewat ATAU status_diklat=1 dan sertifikat null ---
            $belumUploadDiklat = $riwayatDiklat->filter(function ($d) use ($today) {
                return ($d->status_diklat == 0 && $d->tanggal_selesai && $today->greaterThan(Carbon::parse($d->tanggal_selesai)))
                    || ($d->status_diklat == 1 && empty($d->file_sertifikat) && empty($d->arsip));
            });

            if ($belumUploadDiklat->isNotEmpty()) {
                $jumlahBelumUpload = $belumUploadDiklat->count();
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
                        // Use earliest end date or today
                        'tanggal_target' => $belumUploadDiklat->first()->tanggal_selesai ? Carbon::parse($belumUploadDiklat->sortBy('tanggal_selesai')->first()->tanggal_selesai)->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                    ]
                );
            } else {
                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                    ->where('kategori', 'DIKLAT_BELUM_UPLOAD')->delete();
            }
        } else {
            // Tidak punya riwayat diklat → bersihkan tracker diklat
            DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                ->where('kategori', 'DIKLAT_BELUM_UPLOAD')->delete();
        }
    }
}
