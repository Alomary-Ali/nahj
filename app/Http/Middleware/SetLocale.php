<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     */
    public const SUPPORTED_LOCALES = ['ar', 'en'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale || ! in_array($locale, self::SUPPORTED_LOCALES)) {
            $cookieLocale = $request->cookie('locale');
            if ($cookieLocale && in_array($cookieLocale, self::SUPPORTED_LOCALES)) {
                $locale = $cookieLocale;
            } else {
                $locale = config('app.locale', 'ar');
            }
        }

        if (! in_array($locale, self::SUPPORTED_LOCALES)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
