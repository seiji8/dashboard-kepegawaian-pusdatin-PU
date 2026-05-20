<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class DataPegawaiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin_pegawai',
            'password' => Hash::make('password_aman')
        ]);
    }

    /** @test */
    public function admin_can_view_data_pegawai_list()
    {
        $response = $this->actingAs($this->admin)->get(route('data-pegawai'));
        $response->assertStatus(200);
        $response->assertViewIs('data_pegawai.index');
    }

    /** @test */
    public function admin_can_send_manual_notification()
    {
        Mail::fake();

        $pegawai = Pegawai::create([
            'nip' => '777666555',
            'nama' => 'Pegawai Manual Notif',
            'id_pegawai_api' => '600',
            'email' => 'manual@pu.go.id'
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('data-pegawai.send-manual', $pegawai->nip), [
            'subject' => 'Tes Notif Manual',
            'message' => 'Ini isi pesan manual untuk testing.'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verifikasi Mailer
        Mail::assertSent(\App\Mail\ManualNotification::class, function ($mail) use ($pegawai) {
            return $mail->hasTo($pegawai->email);
        });
    }
}
