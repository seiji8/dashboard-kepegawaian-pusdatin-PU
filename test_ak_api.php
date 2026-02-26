<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = env('EHRM_BASE_URL');
$apiKey  = env('EHRM_API_KEY');
$email   = env('EHRM_USER_EMAIL');
$password = env('EHRM_USER_PASS');

$login = Http::post("$baseUrl/user/login", ['email' => $email, 'password' => $password]);
$token = $login->json('session_token');

$resp = Http::withHeaders([
    'X-DreamFactory-Api-Key' => $apiKey,
    'X-DreamFactory-Session-Token' => $token,
])->get("$baseUrl/v1/ehrm/riw/angka-kredit");

$data = $resp->json('resource') ?? [];
echo "Jumlah data AK secara global: " . count($data) . PHP_EOL;

$respPegawai = Http::withHeaders([
    'X-DreamFactory-Api-Key' => $apiKey,
    'X-DreamFactory-Session-Token' => $token,
])->get("$baseUrl/v1/ehrm/pegawai");

$dataPegawai = $respPegawai->json('resource') ?? [];
echo "Jumlah pegawai secara global: " . count($dataPegawai) . PHP_EOL;
