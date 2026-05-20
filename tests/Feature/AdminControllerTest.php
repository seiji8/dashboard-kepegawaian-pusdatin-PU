<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

class AdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function only_super_admin_can_access_admin_list()
    {
        // Admin Pegawai tidak boleh
        $admin = User::factory()->create([
            'role' => 'admin_pegawai',
            'password' => Hash::make('password_aman')
        ]);

        $response = $this->actingAs($admin)->get(route('daftar-admin'));
        $response->assertStatus(403);

        // Super Admin boleh
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'password' => Hash::make('password_aman')
        ]);

        $response2 = $this->actingAs($superAdmin)->get(route('daftar-admin'));
        $response2->assertStatus(200);
        $response2->assertViewIs('daftar_admin.index');
    }

    /** @test */
    public function super_admin_can_add_new_admin_and_password_equals_nip()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'password' => Hash::make('password_aman')
        ]);

        // Buat dummy pegawai
        $pegawai = Pegawai::create([
            'nip' => '999888777',
            'nama' => 'Dummy Pegawai',
            'email' => 'dummy@pu.go.id',
            'id_pegawai_api' => '999888'
        ]);

        $response = $this->actingAs($superAdmin)->postJson(route('daftar-admin.store'), [
            'nip_pegawai' => '999888777'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verifikasi admin masuk ke tabel users
        $newUser = User::where('username', '999888777')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('admin_pegawai', $newUser->role);
        
        // Verifikasi password default = username
        $this->assertTrue(Hash::check('999888777', $newUser->password));
    }
}
