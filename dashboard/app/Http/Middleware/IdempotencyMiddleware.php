<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Enforce Idempotent keys avoiding double transaction occurrences safely.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Idempotency-Key');

        if (!$key) {
            return $next($request);
        }

        $cacheKey = 'idempotency_key_' . $key;

        if (Cache::has($cacheKey)) {
            return response()->json([
                'message' => __('messages.request_duplicated')
            ], Response::HTTP_CONFLICT);
        }

        // Save for 24 hours
        Cache::put($cacheKey, true, 86400);

        return $next($request);
    }
}
