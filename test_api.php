<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = config('ehrm.base_url');
$apiKey  = config('ehrm.api_key');
$email   = config('ehrm.email');
$password = config('ehrm.password');

echo "Login...\n";
$loginResponse = Http::timeout(30)->withHeaders([
    'X-DreamFactory-Api-Key' => $apiKey,
])->post("$baseUrl/user/login", [
    'email' => $email,
    'password' => $password,
]);

$token = $loginResponse->json()['session_token'] ?? null;
if (!$token) {
    echo "Login failed!\n";
    print_r($loginResponse->json());
    exit;
}

echo "Fetching pegawai...\n";
$pegawaiResponse = Http::timeout(60)->withHeaders([
    'X-DreamFactory-Api-Key' => $apiKey,
    'X-DreamFactory-Session-Token' => $token,
])->get("$baseUrl/v1/ehrm/pegawai");

$dataPegawai = $pegawaiResponse->json()['resource'] ?? $pegawaiResponse->json();
echo "Total Pegawai API: " . count($dataPegawai) . "\n";
if (count($dataPegawai) > 0) {
    echo "Sample Pegawai Keys:\n";
    print_r(array_keys($dataPegawai[0]));
    echo "Sample Pegawai Data:\n";
    print_r($dataPegawai[0]);
}

echo "\nFetching riw...\n";
$riwResponse = Http::timeout(60)->withHeaders([
    'X-DreamFactory-Api-Key' => $apiKey,
    'X-DreamFactory-Session-Token' => $token,
])->get("$baseUrl/v1/ehrm/riw");

$dataRiw = $riwResponse->json()['resource'] ?? $riwResponse->json();
echo "Total Riw API: " . count($dataRiw) . "\n";
if (count($dataRiw) > 0) {
    echo "Sample Riw Data:\n";
    print_r($dataRiw[0]);
}

