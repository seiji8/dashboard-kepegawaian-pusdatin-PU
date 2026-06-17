<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use Carbon\Carbon;

class UkomService implements TrackerInterface
{
    /**
     * UKOM Logic is deeply integrated into the KJ_Jafung (Kenaikan Jenjang) evaluation.
     * The evaluation requires comparing current AK against Target AK to determine
     * if the employee is eligible for "Menunggu UKOM".
     *
     * To avoid duplicating the complex Matriks & AK calculation,
     * the tracking logic for UKOM is processed alongside KenaikanJenjangService.
     */
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void
    {
        // No-op: Handled by KenaikanJenjangService
    }
}
