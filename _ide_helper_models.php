<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $nip
 * @property string $kategori
 * @property string $status_saat_ini
 * @property string|null $tanggal_target
 * @property int $dokumen_terupload
 * @property int $dokumen_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KelengkapanDokumen> $kelengkapan_dokumen
 * @property-read int|null $kelengkapan_dokumen_count
 * @property-read \App\Models\Pegawai $pegawai
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereDokumenTerupload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereDokumenTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereStatusSaatIni($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereTanggalTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardTracker whereUpdatedAt($value)
 */
	class DashboardTracker extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nip
 * @property int $dashboard_tracker_id
 * @property string $nama_dokumen
 * @property int $is_uploaded
 * @property string|null $link_file
 * @property string $status_verifikasi
 * @property string|null $keterangan_tolak
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pegawai $pegawai
 * @property-read \App\Models\DashboardTracker $tracker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereDashboardTrackerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereIsUploaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereKeteranganTolak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereLinkFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereNamaDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereStatusVerifikasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelengkapanDokumen whereUpdatedAt($value)
 */
	class KelengkapanDokumen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tipe
 * @property string $deskripsi
 * @property string|null $target_nip
 * @property int|null $user_id
 * @property string $waktu
 * @property-read \App\Models\User|null $admin
 * @property-read \App\Models\Pegawai|null $pegawai
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs whereTargetNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs whereTipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Logs whereWaktu($value)
 */
	class Logs extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $kategori
 * @property string $template_pesan
 * @property int $interval_hari
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $updater
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules whereIntervalHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules whereKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules whereTemplatePesan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiRules whereUpdatedAt($value)
 */
	class NotifikasiRules extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $nip
 * @property string $nama
 * @property string|null $email
 * @property string|null $no_hp
 * @property string|null $jabatan_saat_ini
 * @property string|null $pangkat_saat_ini
 * @property string|null $golongan
 * @property string|null $tmt_pangkat_terakhir
 * @property string|null $tmt_cpns
 * @property string|null $tmt_kgb_terakhir
 * @property string|null $kgb_terakhir
 * @property string|null $sk_pangkat_terakhir
 * @property string|null $sk_cpns
 * @property string|null $sk_struktural
 * @property string|null $tmt_struktural
 * @property string|null $nomor_sk_kp
 * @property string|null $tmt_sk_kp
 * @property string|null $jenjang_pendidikan
 * @property string|null $tgl_mulai_izin_belajar
 * @property string|null $tgl_selesai_izin_belajar
 * @property string|null $link_sk_lulus
 * @property string|null $link_ijazah
 * @property string|null $link_transkrip_nilai
 * @property float|null $nilai_kinerja_tahunan
 * @property string|null $tahun_skp_terakhir
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DashboardTracker> $dashboard_tracker
 * @property-read int|null $dashboard_tracker_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Logs> $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RiwayatAngkaKredit> $riwayat_angka_kredit
 * @property-read int|null $riwayat_angka_kredit_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RiwayatDiklat> $riwayat_diklat
 * @property-read int|null $riwayat_diklat_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereGolongan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereJabatanSaatIni($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereJenjangPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereKgbTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereLinkIjazah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereLinkSkLulus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereLinkTranskripNilai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNilaiKinerjaTahunan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNomorSkKp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai wherePangkatSaatIni($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereSkCpns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereSkPangkatTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereSkStruktural($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTahunSkpTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTglMulaiIzinBelajar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTglSelesaiIzinBelajar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTmtCpns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTmtKgbTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTmtPangkatTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTmtSkKp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTmtStruktural($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereUpdatedAt($value)
 */
	class Pegawai extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $jabatan_asal
 * @property string $pangkat_asal
 * @property float $target_ak
 * @property int $syarat_tahun_min
 * @property string $next_pangkat
 * @property string|null $next_jenjang
 * @property int $is_naik_jenjang
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereIsNaikJenjang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereJabatanAsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereNextJenjang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereNextPangkat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf wherePangkatAsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereSyaratTahunMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereTargetAk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefMatriksJf whereUpdatedAt($value)
 */
	class RefMatriksJf extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nip
 * @property string $tahun
 * @property string $triwulan
 * @property float $nilai_konversi
 * @property string|null $keterangan_skp
 * @property int $is_processed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pegawai $pegawai
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereIsProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereKeteranganSkp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereNilaiKonversi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereTriwulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatAngkaKredit whereUpdatedAt($value)
 */
	class RiwayatAngkaKredit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nip
 * @property string $nama_diklat
 * @property string|null $tanggal_mulai
 * @property string|null $tanggal_selesai
 * @property string|null $nomor_sertifikat
 * @property string|null $tanggal_sertifikat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pegawai $pegawai
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereNamaDiklat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereNomorSertifikat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereTanggalMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereTanggalSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereTanggalSertifikat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiwayatDiklat whereUpdatedAt($value)
 */
	class RiwayatDiklat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $username
 * @property string $nama_lengkap
 * @property string|null $email
 * @property string $password
 * @property string $role
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNamaLengkap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent {}
}

