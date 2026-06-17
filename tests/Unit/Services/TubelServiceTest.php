<?php

namespace Tests\Unit\Services;

use App\Models\DashboardTracker;
use App\Models\Pegawai;
use App\Models\RiwayatTubel;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use App\Services\Tracker\TubelService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TubelServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /** @test */
    public function it_sets_status_to_sedang_tubel_if_not_close_to_end()
    {
        $pegawai = Pegawai::create([
            'nip' => '900111222',
            'nama' => 'Pegawai Tubel',
            'id_pegawai_api' => '900',
        ]);

        RiwayatTubel::create([
            'nip' => $pegawai->nip,
            'tanggal_mulai' => Carbon::now()->subMonths(6)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(90)->toDateString(), // Masih > 60 hari
        ]);

        $service = new TubelService;
        $usulan = [];
        $service->process($pegawai, Carbon::now(), $usulan);

        $tracker = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
            ->where('kategori', 'TUBEL')
            ->first();

        $this->assertNotNull($tracker);
        $this->assertEquals('Sedang Tubel', $tracker->status_saat_ini);

        // Memastikan dokumen otomatis dibuat
        $this->assertEquals(3, \App\Models\KelengkapanDokumen::where('dashboard_tracker_id', $tracker->id)->count());
    }

    /** @test */
    public function it_triggers_notification_when_status_changes_to_proses_pengaktifan()
    {
        $admin = User::factory()->create([
            'role' => 'admin_pegawai',
            'email' => 'admin_tubel@pu.go.id',
        ]);

        $pegawai = Pegawai::create([
            'nip' => '900111333',
            'nama' => 'Pegawai Hampir Lulus Tubel',
            'id_pegawai_api' => '901',
        ]);

        // Sisa waktu <= 60 hari
        RiwayatTubel::create([
            'nip' => $pegawai->nip,
            'tanggal_mulai' => Carbon::now()->subYears(2)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        DashboardTracker::create([
            'pegawai_id' => $pegawai->id_pegawai_api,
            'kategori' => 'TUBEL',
            'status_saat_ini' => 'Sedang Tubel',
        ]);

        $service = new TubelService;
        $usulan = [];
        $service->process($pegawai, Carbon::now(), $usulan);

        $tracker = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
            ->where('kategori', 'TUBEL')
            ->first();

        $this->assertNotNull($tracker);
        $this->assertEquals('Proses Pengaktifan', $tracker->status_saat_ini);

        // Verifikasi notifikasi dikirim ke admin
        Notification::assertSentTo(
            [$admin],
            SystemAlertNotification::class,
            function ($notification, $channels) use ($pegawai) {
                return str_contains($notification->subjectLine, $pegawai->nama);
            }
        );
    }

    /** @test */
    public function it_deletes_tracker_if_no_active_tubel()
    {
        $pegawai = Pegawai::create([
            'nip' => '900111444',
            'nama' => 'Pegawai Lulus Tubel',
            'id_pegawai_api' => '902',
        ]);

        // Buat tracker usang
        DashboardTracker::create([
            'pegawai_id' => $pegawai->id_pegawai_api,
            'kategori' => 'TUBEL',
            'status_saat_ini' => 'Sedang Tubel',
        ]);

        // Riwayat tubel sudah lama selesai
        RiwayatTubel::create([
            'nip' => $pegawai->nip,
            'tanggal_mulai' => Carbon::now()->subYears(3)->toDateString(),
            'tanggal_selesai' => Carbon::now()->subMonths(1)->toDateString(),
        ]);

        $service = new TubelService;
        $usulan = [];
        $service->process($pegawai, Carbon::now(), $usulan);

        $trackerCount = DashboardTracker::where('pegawai_id', $pegawai->id_pegawai_api)
            ->where('kategori', 'TUBEL')
            ->count();

        $this->assertEquals(0, $trackerCount);
    }
}
