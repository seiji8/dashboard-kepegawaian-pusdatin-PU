<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * Menambahkan Security Headers ke setiap HTTP response
     * untuk mencegah serangan Clickjacking, MIME Sniffing, dan XSS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah Clickjacking: Web tidak bisa di-iframe di web orang lain (kecuali origin yang sama)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Mencegah MIME Sniffing: Browser wajib patuhi content-type dari server
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Aktifkan filter XSS bawaan browser
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Batasi info referer yang bocor ke website lain
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Paksa HTTPS (hanya aktif di production)
        // $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
