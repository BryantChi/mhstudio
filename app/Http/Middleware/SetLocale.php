<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales.
     */
    protected array $supportedLocales = ['zh_TW', 'en'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    /**
     * Resolve the locale from various sources.
     */
    protected function resolveLocale(Request $request): string
    {
        // 1. Check query parameter (highest priority) and store in session
        if ($request->has('locale') && in_array($request->query('locale'), $this->supportedLocales)) {
            $locale = $request->query('locale');

            // 只在 locale 變更時才寫入 session
            if (session('locale') !== $locale) {
                session()->put('locale', $locale);
            }

            return $locale;
        }

        // 2. Check session
        if (session()->has('locale') && in_array(session('locale'), $this->supportedLocales)) {
            return session('locale');
        }

        // 3. Check cookie
        if ($request->hasCookie('locale') && in_array($request->cookie('locale'), $this->supportedLocales)) {
            $locale = $request->cookie('locale');
            session()->put('locale', $locale);

            return $locale;
        }

        // 4. Fall back to config
        return config('app.locale', 'zh_TW');
    }
}
