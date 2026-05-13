<?php

namespace App\Services\Tracker;

use App\Models\Pegawai;
use Carbon\Carbon;

interface TrackerInterface
{
    /**
     * Process tracker logic for a given employee.
     *
     * @param Pegawai $pegawai
     * @param Carbon $today
     * @param array $daftarUsulanBaru
     * @param array $context
     * @return void
     */
    public function process(Pegawai $pegawai, Carbon $today, array &$daftarUsulanBaru, array $context = []): void;
}
