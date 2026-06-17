<?php

namespace Tests\Unit\Services;

use App\Models\DashboardTracker;
use App\Models\Pegawai;
use App\Services\Tracker\KgbTrackerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KgbTrackerServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_creates_kgb_tracker_if_2_years_passed()
    {
        $pegawai = Pegawai::create([
            'nip' => '900333111',
            'nama' => 'Pegawai KGB',
            'id_pegawai_api' => '700',
            'tmt_kgb_terakhir' => Carbon::now()->subYears(2)->toDateString(),
        ]);

        $service = new KgbTrackerService;
        $usulan = [];
        $service->process($pegawai, Carbon::now(), $usulan);

        $tracker = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
            ->where('kategori', 'KGB')
            ->first();

        $this->assertNotNull($tracker);
        $this->assertEquals('Usulan', $tracker->status_saat_ini);
    }
}
