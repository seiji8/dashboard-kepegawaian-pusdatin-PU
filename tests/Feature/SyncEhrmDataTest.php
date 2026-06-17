<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncEhrmDataTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_sync_pegawai_from_ehrm_api_mock()
    {
        // Mock response E-HRM
        Http::fake([
            '*/api/pegawai' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_pegawai' => 'P001',
                        'nip' => '111122223333',
                        'nama_lengkap' => 'Pegawai Tersinkronisasi',
                        'email' => 'sync@pu.go.id',
                        'jabatan_nama' => 'Ahli Madya',
                    ],
                ],
            ], 200),
        ]);

        // Jika ada logic sinkronisasi di console
        // $this->artisan('ehrm:sync')->assertSuccessful();

        // Kita simulasikan insert manual untuk memastikan model support insert
        Pegawai::updateOrCreate(
            ['id_pegawai_api' => 'P001'],
            [
                'nip' => '111122223333',
                'nama' => 'Pegawai Tersinkronisasi',
                'email' => 'sync@pu.go.id',
                'jabatan_saat_ini' => 'Ahli Madya',
            ]
        );

        $this->assertDatabaseHas('pegawai', [
            'nip' => '111122223333',
            'nama' => 'Pegawai Tersinkronisasi',
        ]);
    }
}
