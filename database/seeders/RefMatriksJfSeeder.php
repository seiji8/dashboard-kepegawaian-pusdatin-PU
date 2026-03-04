<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefMatriksJf;

class RefMatriksJfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // KEAHLIAN
        $keahlian = [
            // Ahli Pertama
            [
                'jabatan_asal' => 'Ahli Pertama',
                'pangkat_asal' => 'III/a',
                'target_ak' => 50,
                'next_pangkat' => 'III/b',
                'next_jenjang' => 'Ahli Pertama',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 12.5,
            ],
            [
                'jabatan_asal' => 'Ahli Pertama',
                'pangkat_asal' => 'III/b',
                'target_ak' => 50,
                'next_pangkat' => 'III/c',
                'next_jenjang' => 'Ahli Muda',
                'is_naik_jenjang' => true,
                'koefisien_tahunan' => 12.5,
            ],
            // TRANSISI: Sudah naik jenjang ke Ahli Muda, tapi pangkat masih III/b → usulkan naik ke III/c
            [
                'jabatan_asal' => 'Ahli Muda',
                'pangkat_asal' => 'III/b',
                'target_ak' => 0,
                'next_pangkat' => 'III/c',
                'next_jenjang' => 'Ahli Muda',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 25,
            ],
            // Ahli Muda
            [
                'jabatan_asal' => 'Ahli Muda',
                'pangkat_asal' => 'III/c',
                'target_ak' => 100,
                'next_pangkat' => 'III/d',
                'next_jenjang' => 'Ahli Muda',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 25,
            ],
            [
                'jabatan_asal' => 'Ahli Muda',
                'pangkat_asal' => 'III/d',
                'target_ak' => 100,
                'next_pangkat' => 'IV/a',
                'next_jenjang' => 'Ahli Madya',
                'is_naik_jenjang' => true,
                'koefisien_tahunan' => 25,
            ],
            // TRANSISI: Sudah naik jenjang ke Ahli Madya, tapi pangkat masih III/d → usulkan naik ke IV/a
            [
                'jabatan_asal' => 'Ahli Madya',
                'pangkat_asal' => 'III/d',
                'target_ak' => 0,
                'next_pangkat' => 'IV/a',
                'next_jenjang' => 'Ahli Madya',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 37.5,
            ],
            // Ahli Madya
            [
                'jabatan_asal' => 'Ahli Madya',
                'pangkat_asal' => 'IV/a',
                'target_ak' => 150,
                'next_pangkat' => 'IV/b',
                'next_jenjang' => 'Ahli Madya',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 37.5,
            ],
            [
                'jabatan_asal' => 'Ahli Madya',
                'pangkat_asal' => 'IV/b',
                'target_ak' => 150,
                'next_pangkat' => 'IV/c',
                'next_jenjang' => 'Ahli Madya',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 37.5,
            ],
            [
                'jabatan_asal' => 'Ahli Madya',
                'pangkat_asal' => 'IV/c',
                'target_ak' => 150,
                'next_pangkat' => 'IV/d',
                'next_jenjang' => 'Ahli Utama',
                'is_naik_jenjang' => true,
                'koefisien_tahunan' => 37.5,
            ],
            // TRANSISI: Sudah naik jenjang ke Ahli Utama, tapi pangkat masih IV/c → usulkan naik ke IV/d
            [
                'jabatan_asal' => 'Ahli Utama',
                'pangkat_asal' => 'IV/c',
                'target_ak' => 0,
                'next_pangkat' => 'IV/d',
                'next_jenjang' => 'Ahli Utama',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 50,
            ],
            // Ahli Utama
            [
                'jabatan_asal' => 'Ahli Utama',
                'pangkat_asal' => 'IV/d',
                'target_ak' => 200,
                'next_pangkat' => 'IV/e',
                'next_jenjang' => 'Ahli Utama',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 50,
            ],
        ];

        // KETERAMPILAN
        $keterampilan = [
            // Pemula
            [
                'jabatan_asal' => 'Pemula',
                'pangkat_asal' => 'II/a',
                'target_ak' => 15,
                'next_pangkat' => 'II/b',
                'next_jenjang' => 'Terampil',
                'is_naik_jenjang' => true,
                'koefisien_tahunan' => 3.75,
            ],
            // TRANSISI: Sudah naik jenjang ke Terampil, tapi pangkat masih II/a → usulkan naik ke II/b
            [
                'jabatan_asal' => 'Terampil',
                'pangkat_asal' => 'II/a',
                'target_ak' => 0,
                'next_pangkat' => 'II/b',
                'next_jenjang' => 'Terampil',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 5,
            ],
            // Terampil
            [
                'jabatan_asal' => 'Terampil',
                'pangkat_asal' => 'II/b',
                'target_ak' => 20,
                'next_pangkat' => 'II/c',
                'next_jenjang' => 'Terampil',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 5,
            ],
            [
                'jabatan_asal' => 'Terampil',
                'pangkat_asal' => 'II/c',
                'target_ak' => 20,
                'next_pangkat' => 'II/d',
                'next_jenjang' => 'Terampil',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 5,
            ],
            [
                'jabatan_asal' => 'Terampil',
                'pangkat_asal' => 'II/d',
                'target_ak' => 20,
                'next_pangkat' => 'III/a',
                'next_jenjang' => 'Mahir',
                'is_naik_jenjang' => true,
                'koefisien_tahunan' => 5,
            ],
            // TRANSISI: Sudah naik jenjang ke Mahir, tapi pangkat masih II/d → usulkan naik ke III/a
            [
                'jabatan_asal' => 'Mahir',
                'pangkat_asal' => 'II/d',
                'target_ak' => 0,
                'next_pangkat' => 'III/a',
                'next_jenjang' => 'Mahir',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 12.5,
            ],
            // Mahir
            [
                'jabatan_asal' => 'Mahir',
                'pangkat_asal' => 'III/a',
                'target_ak' => 50,
                'next_pangkat' => 'III/b',
                'next_jenjang' => 'Mahir',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 12.5,
            ],
            [
                'jabatan_asal' => 'Mahir',
                'pangkat_asal' => 'III/b',
                'target_ak' => 50,
                'next_pangkat' => 'III/c',
                'next_jenjang' => 'Penyelia',
                'is_naik_jenjang' => true,
                'koefisien_tahunan' => 12.5,
            ],
            // TRANSISI: Sudah naik jenjang ke Penyelia, tapi pangkat masih III/b → usulkan naik ke III/c
            [
                'jabatan_asal' => 'Penyelia',
                'pangkat_asal' => 'III/b',
                'target_ak' => 0,
                'next_pangkat' => 'III/c',
                'next_jenjang' => 'Penyelia',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 25,
            ],
            // Penyelia
            [
                'jabatan_asal' => 'Penyelia',
                'pangkat_asal' => 'III/c',
                'target_ak' => 100,
                'next_pangkat' => 'III/d',
                'next_jenjang' => 'Penyelia',
                'is_naik_jenjang' => false,
                'koefisien_tahunan' => 25,
            ],
        ];

        $allData = array_merge($keahlian, $keterampilan);

        foreach ($allData as $data) {
            RefMatriksJf::updateOrCreate(
                [
                    'jabatan_asal' => $data['jabatan_asal'],
                    'pangkat_asal' => $data['pangkat_asal'],
                ],
                $data
            );
        }
    }
}
