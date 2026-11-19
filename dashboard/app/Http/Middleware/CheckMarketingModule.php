<?php

namespace App\Http\Middleware;

use App\Settings\MarketingSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMarketingModule
{
    public function __construct(private readonly MarketingSettings $marketingSettings) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! isset($this->marketingSettings->$module) || ! $this->marketingSettings->$module) {
            abort(403, "The marketing module [{$module}] is currently disabled.");
        }

        return $next($request);
    }
}
