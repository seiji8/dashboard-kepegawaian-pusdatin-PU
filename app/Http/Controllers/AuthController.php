<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi Input (Wajib format email valid)
        $request->validate([
            'email' => ['required', 'email'], // Validasi format email
            'password' => ['required', 'string'],
        ]);

        // 2. SECURITY: Cek Rate Limiter (Anti Brute Force)
        // Kunci throttle berdasarkan email + IP address
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [ // Error dikirim ke field email
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // 3. Proses Login
        $credentials = $request->only('email', 'password'); // Ambil email & password
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Jika SUKSES
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        // 4. Jika GAGAL
        RateLimiter::hit($throttleKey);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'), // Pesan error muncul di bawah input email
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}