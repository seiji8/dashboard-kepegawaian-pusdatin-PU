<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'nama_lengkap' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),
            'role' => 'admin_pegawai',
        ];
    }
}
