<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use App\Models\DashboardTracker;
use App\Models\User;
use App\Models\NotifikasiRules;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Helpers\ActivityLogger;

class KgbTrackerService implements TrackerInterface
{
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        // Skip dummy/test data as they are manually seeded and don't need KGB calculation
        if (str_contains(strtolower($pegawai->id_pegawai_api ?? ''), 'dummy') || 
            str_contains(strtolower($pegawai->nip ?? ''), 'dummy')) {
            return;
        }

        $leadCheckDays = $context['leadCheckDays'] ?? 60;
        $freqUploadDays = $context['freqUploadDays'] ?? 1;
        $force = $context['force'] ?? false;

        if ($pegawai->tmt_kgb_terakhir) {
            $nextKGB = Carbon::parse($pegawai->tmt_kgb_terakhir)->addYears(2);
            $startNotify = $nextKGB->copy()->subDays($leadCheckDays);

            $status = 'Aman';
            $keterangan = 'Masih dalam masa aman';

            $existingTracker = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                ->where('kategori', 'KGB')
                                ->first();
            
            $isConfirmed = $existingTracker && $existingTracker->dikonfirmasi_at;
            $isUploaded  = $existingTracker && ($existingTracker->dokumen_terupload >= $existingTracker->dokumen_total);
            $currentStatus = $existingTracker ? $existingTracker->status_saat_ini : null;
            
            if ($today->greaterThanOrEqualTo($nextKGB) && $isUploaded) {
                $status = 'Aman';
            } elseif ($currentStatus === 'Upload E-HRM' || $isConfirmed) {
                $status = 'Upload E-HRM';
                $keterangan = 'TTE Selesai. Menunggu upload SK E-HRM.';
            } elseif ($currentStatus === 'Proses') {
                $status = 'Proses';
                $keterangan = 'Usulan sedang diproses (TTE) oleh admin.';
            } elseif ($today->greaterThanOrEqualTo($startNotify)) {
                $status = 'Usulan';
                $keterangan = $today->greaterThanOrEqualTo($nextKGB) ? 'Masa KGB telah tiba. Segera ajukan usulan KGB.' : 'Segera ajukan usulan KGB.';
            } else {
                $status = 'Aman';
            }

            if ($status != 'Aman') {
                $tracker = DashboardTracker::updateOrCreate(
                    ['pegawai_id' => $pegawai->id_pegawai_api, 'kategori' => 'KGB'],
                    [
                        'tanggal_target'  => $nextKGB->format('Y-m-d'),
                        'status_saat_ini' => $status,
                        'keterangan'      => $keterangan,
                        'dokumen_total'   => 1,
                    ]
                );

                if ($tracker->wasChanged('status_saat_ini')) {
                    if ($status == 'Upload E-HRM' || $status == 'Usulan') {
                        $tracker->update(['notified_at' => null]);
                    }
                }

                $lastNotifDate = $tracker->notified_at ? Carbon::parse($tracker->notified_at) : null;
                $daysSinceLast = $lastNotifDate ? $lastNotifDate->diffInDays($today) : 9999;
                
                $isDueForUploadNotif = ($daysSinceLast >= $freqUploadDays);
                
                if ($force) {
                    $isDueForUploadNotif = true;
                } else {
                    if ($lastNotifDate && $lastNotifDate->isToday()) {
                        $isDueForUploadNotif = false;
                    }
                }

                if ($status == 'Usulan' && !$tracker->notified_at) {
                    $daftarUsulanBaru[] = [
                        'nama' => $pegawai->nama,
                        'nip' => $pegawai->nip,
                        'kategori' => 'KGB'
                    ];
                    $tracker->update(['notified_at' => now()]);
                } elseif ($status == 'Upload E-HRM') {
                    if ($isDueForUploadNotif) {
                        $notifiable = User::where('email', $pegawai->email)->first();
                        if (!$notifiable && $pegawai->email) {
                            $notifiable = Notification::route('mail', $pegawai->email);
                        }
                        if ($notifiable) {
                            $tmt = Carbon::parse($tracker->tanggal_target);
                            $bulanTahun = $tmt->isoFormat('MMMM Y');
                            
                            $subject = '🔔 Notifikasi KGB: SK KGB Sudah Terbit';
                            
                            $ruleUpload = NotifikasiRules::where('kategori', 'KGB Upload Dokumen')->first();
                            if ($ruleUpload) {
                                $content = str_replace(
                                    ['{nama}', '{nip}', '{deadline}'],
                                    [$pegawai->nama, $pegawai->nip, $tmt->format('d-m-Y')],
                                    $ruleUpload->template_pesan
                                );
                            } else {
                                $content = "Waktunya proses KGB untuk periode {$bulanTahun}.\n\nSegera lengkapi berkas dan upload SK KGB Terakhir Anda ke sistem sekarang agar dapat diproses lebih lanjut oleh admin.";
                            }
                            
                            $notifiable->notify(new SystemAlertNotification($pegawai, $subject, $content));
                            
                            $tracker->update(['notified_at' => now()]); 
                            ActivityLogger::logSystem("Mengirim notifikasi KGB Upload E-HRM ke pegawai {$pegawai->nama}", $pegawai->nip);
                        }
                    }
                }
            } else {
                DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
                                ->where('kategori', 'KGB')
                                ->delete();
            }
        }
    }
}
