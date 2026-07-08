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
        $locale = $request->query('lang', $request->query('locale'));
        $hasSession = $request->hasSession();

        if (is_string($locale) && in_array($locale, ['en', 'vi'], true)) {
            if ($hasSession) {
                $request->session()->put('locale', $locale);
            }
        } else {
            $locale = $hasSession
                ? $request->session()->get('locale', config('app.locale'))
                : config('app.locale');
            if (! in_array($locale, ['en', 'vi'], true)) {
                $locale = config('app.locale');
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
