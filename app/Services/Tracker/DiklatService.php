<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use App\Models\DashboardTracker;
use Carbon\Carbon;

class DiklatService implements TrackerInterface
{
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        $riwayatDiklat = \App\Models\RiwayatDiklat::where('nip', $pegawai->nip)->get();

        if ($riwayatDiklat->isNotEmpty()) {
            // --- HUTANG LAPORAN: status_diklat=0 AND tanggal_selesai sudah lewat ---
            $hutangDiklat = $riwayatDiklat->filter(function ($d) use ($today) {
                return $d->status_diklat == 0
                    && $d->tanggal_selesai
                    && $today->greaterThan(Carbon::parse($d->tanggal_selesai));
            });

            if ($hutangDiklat->isNotEmpty()) {
                $jumlahHutang = $hutangDiklat->count();
                $keterangan = $jumlahHutang == 1
                    ? 'Sertifikat diklat belum diupload ke E-HRM'
                    : $jumlahHutang . ' sertifikat diklat belum diupload ke E-HRM';

                $trackerHutang = DashboardTracker::updateOrCreate(
                    ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'DIKLAT_HUTANG'],
                    [
                        'status_saat_ini' => 'Upload E-HRM',
                        'keterangan' => $keterangan,
                        'dokumen_total' => $jumlahHutang,
                        'dokumen_terupload' => 0,
                        'tanggal_target' => Carbon::parse($hutangDiklat->sortBy('tanggal_selesai')->first()->tanggal_selesai)->format('Y-m-d'),
                    ]
                );
            } else {
                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                    ->where('kategori', 'DIKLAT_HUTANG')->delete();
            }

            // --- ANOMALI DOKUMEN: status_diklat=1 tapi arsip/nomor_sertifikat null ---
            $anomaliDiklat = $riwayatDiklat->filter(function ($d) {
                return $d->status_diklat == 1
                    && (empty($d->arsip) || empty($d->nomor_sertifikat) || $d->nomor_sertifikat === '-');
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
