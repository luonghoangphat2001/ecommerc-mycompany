<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID')
            ?: $request->header('X-Correlation-ID')
            ?: (string) Str::uuid();

        if (!preg_match('/^[A-Za-z0-9._-]{1,128}$/', $requestId)) {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('request_id', $requestId);
        $request->attributes->set('correlation_id', $requestId);
        Log::shareContext([
            'request_id' => $requestId,
            'correlation_id' => $requestId,
        ]);

        $response = $next($request);

        // Optionally, return it in headers for client-side tracing
        if (method_exists($response, 'header')) {
            $response->header('X-Request-ID', $requestId);
            $response->header('X-Correlation-ID', $requestId);
        }

        return $response;
    }
}
