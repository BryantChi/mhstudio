<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * 為所有回應加上安全性標頭
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 防止點擊劫持
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 防止 MIME 類型嗅探
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS 保護
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer 政策
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 權限政策（限制敏感 API 使用）
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // 強制 HTTPS（僅在非 local 環境啟用）
        if (app()->environment('production', 'staging')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy（寬鬆模式，允許 CDN 和 inline）
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https: blob:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
