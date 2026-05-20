<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function user_can_view_login_page()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function authenticated_user_is_redirected_away_from_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_aman')
        ]);
        
        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'testuser@pu.go.id',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@pu.go.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@pu.go.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_aman')
        ]);

        $this->actingAs($user);
        
        $response = $this->post('/logout');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
