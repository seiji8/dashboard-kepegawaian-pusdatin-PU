<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use Carbon\Carbon;

interface TrackerInterface
{
    /**
     * Process tracker logic for a given employee.
     */
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void;
}
