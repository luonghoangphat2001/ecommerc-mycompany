<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMarketingModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $marketingSettings = app(\App\Settings\MarketingSettings::class);

        if (!isset($marketingSettings->$module) || !$marketingSettings->$module) {
            abort(403, "The marketing module [{$module}] is currently disabled.");
        }

        return $next($request);
    }
}
