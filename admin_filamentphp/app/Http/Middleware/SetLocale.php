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
        $locale = $request->query('lang', $request->query('locale', $request->header('Accept-Language')));
        if (!$locale || !in_array($locale, ['en', 'vi'])) {
            $locale = config('app.locale');
        }
        app()->setLocale($locale);

        return $next($request);
    }
}
