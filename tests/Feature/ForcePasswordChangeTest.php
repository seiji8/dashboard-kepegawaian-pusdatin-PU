<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function new_admin_must_change_password()
    {
        // Admin baru: password sama dengan username (NIP)
        $user = User::factory()->create([
            'username' => '1234567890',
            'password' => Hash::make('1234567890'),
            'role' => 'admin_pegawai',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Harus dialihkan ke halaman ganti password
        $response->assertRedirect(route('password.force-change'));
        $response->assertSessionHas('warning', 'Anda wajib mengubah password default Anda sebelum melanjutkan.');
    }

    /** @test */
    public function admin_can_access_dashboard_after_changing_password()
    {
        // Admin yang sudah mengganti password
        $user = User::factory()->create([
            'username' => '1234567890',
            'password' => Hash::make('password_baru_yang_aman'),
            'role' => 'admin_pegawai',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Harus berhasil mengakses dashboard (atau setidaknya tidak dialihkan ke force-change)
        $response->assertStatus(200);
    }

    /** @test */
    public function force_change_password_endpoint_updates_password()
    {
        $user = User::factory()->create([
            'username' => '1234567890',
            'password' => Hash::make('1234567890'),
            'role' => 'admin_pegawai',
        ]);

        $response = $this->actingAs($user)->post(route('password.force-change.update'), [
            'current_password' => '1234567890',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Harus berhasil kembali ke dashboard
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Password berhasil diubah.');

        // Verifikasi password telah berubah di database
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function force_change_password_fails_if_current_password_is_wrong()
    {
        $user = User::factory()->create([
            'username' => '1234567890',
            'password' => Hash::make('1234567890'),
            'role' => 'admin_pegawai',
        ]);

        $response = $this->actingAs($user)->post(route('password.force-change.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Harus mengembalikan error
        $response->assertSessionHasErrors('current_password');

        // Verifikasi password TIDAK berubah
        $this->assertTrue(Hash::check('1234567890', $user->fresh()->password));
    }
}
