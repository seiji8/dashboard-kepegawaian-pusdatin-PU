<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi API e-HRM KemenPU
    |--------------------------------------------------------------------------
    |
    | File ini menjembatani variabel .env agar aman dibaca melalui
    | fungsi config() — yang tetap berfungsi setelah config:cache.
    |
    */

    // API Lama (DreamFactory Gateway)
    'base_url'  => env('EHRM_BASE_URL'),
    'api_key'   => env('EHRM_API_KEY'),
    'email'     => env('EHRM_USER_EMAIL'),
    'password'  => env('EHRM_USER_PASS'),

    // API Baru (modules-api)
    'new_token' => env('EHRM_NEW_TOKEN'),

];
