<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;

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

            // LOG: Login berhasil
            $user = Auth::user();
            ActivityLogger::logAdminAction(
                'Login berhasil oleh ' . $user->nama_lengkap . ' (' . $user->email . ')'
            );

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
        // LOG: Logout sebelum user session dihapus
        $user = Auth::user();
        if ($user) {
            ActivityLogger::logAdminAction(
                'Logout oleh ' . $user->nama_lengkap . ' (' . $user->email . ')'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // FORGOT PASSWORD METHODS
    
    public function showForgotPasswordForm()
    {
        return view('auth.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar dalam sistem.']);
        }

        // Generate token
        $token = Str::random(64);

        // Delete old tokens for this email
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // Send email (for now, we'll just redirect with success message)
        // In production, you would send an actual email here
        // Mail::send('emails.password_reset', ['token' => $token], function($message) use($request){
        //     $message->to($request->email);
        //     $message->subject('Reset Password');
        // });

        // Log activity
        ActivityLogger::logSystem('Reset password diminta untuk email: ' . $request->email);

        return back()->with('status', 'Link reset password telah dikirim ke email Anda! (Token: ' . $token . ')');
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset_password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Check if token exists and is valid
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kadaluarsa.']);
        }

        // Check if token matches
        if (!Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'Token reset password tidak valid.']);
        }

        // Check if token is expired (valid for 60 minutes)
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            return back()->withErrors(['email' => 'Token reset password sudah kadaluarsa.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Log activity
        ActivityLogger::logAdminAction('Reset password berhasil untuk: ' . $user->nama_lengkap);

        return redirect()->route('login')->with('status', 'Password berhasil direset! Silakan login dengan password baru Anda.');
    }
    // CHANGE PASSWORD (AUTHENTICATED USER)
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $user = Auth::user();

        // 1. Verifikasi Password Lama
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama yang Anda masukkan salah.'],
            ]);
        }

        // 2. Update Password Baru
        /** @var \App\Models\User $user */
        $user->password = Hash::make($request->new_password);
        $user->save();

        // 3. Log Aktivitas
        ActivityLogger::logAdminAction(
            'Mengubah password akun sendiri (' . $user->nama_lengkap . ')'
        );

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah!'
        ]);
    }
}