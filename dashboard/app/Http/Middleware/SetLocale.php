<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['vi', 'en'];
        $hasSession = $request->hasSession();

        $queryLocale = $request->query('locale') ?? $request->query('lang');
        if (is_string($queryLocale)) {
            $queryLocale = strtolower(trim($queryLocale));
        }

        $xLocale = $request->header('X-Locale');
        if (is_string($xLocale)) {
            $xLocale = substr(strtolower(trim($xLocale)), 0, 2);
        }

        $acceptHeader = $request->header('Accept-Language');
        $acceptLocale = is_string($acceptHeader) && trim($acceptHeader) !== ''
            ? substr(strtolower(trim(explode(',', $acceptHeader)[0])), 0, 2)
            : null;

        $sessionLocale = $hasSession ? $request->session()->get('locale') : null;

        $candidates = $request->is('api/*')
            ? [$queryLocale, $xLocale, $acceptLocale, $sessionLocale]
            : [$queryLocale, $xLocale, $sessionLocale, $acceptLocale];

        $locale = null;
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                $locale = $candidate;
                break;
            }
        }

        if (! $locale) {
            $defaultLocale = config('app.locale', 'vi');
            $locale = in_array($defaultLocale, $supported, true) ? $defaultLocale : 'vi';
        }

        if ($hasSession && is_string($queryLocale) && in_array($queryLocale, $supported, true)) {
            $request->session()->put('locale', $locale);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
