<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\DashboardTracker;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

class DashboardControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup admin yang sudah ganti password agar lolos middleware ForcePasswordChange
        $this->admin = User::factory()->create([
            'username' => 'admin_test',
            'role' => 'admin_pegawai',
            'password' => Hash::make('password_aman')
        ]);
        
        $this->pegawai = Pegawai::create([
            'nip' => '123123123',
            'nama' => 'Pekerja Test',
            'id_pegawai_api' => '123'
        ]);
    }

    /** @test */
    public function dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function move_to_ukom_changes_tracker_category()
    {
        $tracker = DashboardTracker::create([
            'pegawai_id' => $this->pegawai->id_pegawai_api,
            'kategori' => 'KJ_Jafung',
            'status_saat_ini' => 'Menunggu UKOM',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('tracker.move-to-ukom', $tracker->id));

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verifikasi database update
        $tracker->refresh();
        $this->assertEquals('UKOM', $tracker->kategori);
        $this->assertEquals('Usulan', $tracker->status_saat_ini);
    }

    /** @test */
    public function set_kelulusan_ukom_updates_tracker()
    {
        $tracker = DashboardTracker::create([
            'pegawai_id' => $this->pegawai->id_pegawai_api,
            'kategori' => 'UKOM',
            'status_saat_ini' => 'Proses',
        ]);

        // Uji Lulus
        $response = $this->actingAs($this->admin)->postJson(route('tracker.set-kelulusan-ukom', $tracker->id), [
            'lulus' => true
        ]);

        $response->assertStatus(200);
        
        $tracker->refresh();
        $this->assertEquals('KJ_Jafung', $tracker->kategori);
        $this->assertEquals('Usulan', $tracker->status_saat_ini);

        // Uji Tidak Lulus
        $response2 = $this->actingAs($this->admin)->postJson(route('tracker.set-kelulusan-ukom', $tracker->id), [
            'lulus' => false
        ]);

        $response2->assertStatus(200);
        
        $tracker->refresh();
        $this->assertEquals('Tidak Lulus UKOM', $tracker->keterangan);
    }
}
