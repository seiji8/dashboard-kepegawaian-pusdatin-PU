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
            // Nonaktifkan pelacakan DIKLAT_HUTANG dan hapus jika ada sisa data lama
            DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                ->where('kategori', 'DIKLAT_HUTANG')->delete();

            // --- ANOMALI DOKUMEN: status_diklat=1 tapi file_sertifikat dan arsip null ---
            $anomaliDiklat = $riwayatDiklat->filter(function ($d) {
                return $d->status_diklat == 1
                    && empty($d->file_sertifikat) && empty($d->arsip);
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
    }
}
