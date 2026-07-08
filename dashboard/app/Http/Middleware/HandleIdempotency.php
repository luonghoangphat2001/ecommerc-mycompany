<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HandleIdempotency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('POST') || !$request->hasHeader('X-Idempotency-Key')) {
            return $next($request);
        }

        $key = 'idempotency:' . $request->header('X-Idempotency-Key');
        
        // If the key is already in the cache, return the cached response.
        if ($cachedResponse = Cache::get($key)) {
            return response()->json($cachedResponse['content'], $cachedResponse['status'])
                ->header('X-Idempotency-Cached', 'true');
        }

        $response = $next($request);

        // Cache the response if it was successful (2xx).
        if ($response->isSuccessful()) {
            Cache::put($key, [
                'content' => json_decode($response->getContent(), true),
                'status' => $response->getStatusCode(),
            ], now()->addHours(24));
        }

        return $response;
    }
}
