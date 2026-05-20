<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Cek jika password masih sama dengan username (NIP)
            if (Hash::check($user->username, $user->password)) {
                // Jangan redirect jika sedang berada di halaman change password
                if (!$request->routeIs('password.force-change') && 
                    !$request->routeIs('password.force-change.update') && 
                    !$request->routeIs('logout')) {
                    return redirect()->route('password.force-change')->with('warning', 'Anda wajib mengubah password default Anda sebelum melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}
