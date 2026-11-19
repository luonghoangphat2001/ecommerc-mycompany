<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HandleIdempotency
{
    private const DEFAULT_TTL_SECONDS = 86400;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT') && ! $request->isMethod('PATCH')) {
            return $next($request);
        }

        $idempotencyKey = trim((string) $request->header('Idempotency-Key'));
        if ($idempotencyKey === '') {
            return $next($request);
        }

        if (strlen($idempotencyKey) > 128) {
            return response()->json(['message' => 'The Idempotency-Key header must not exceed 128 characters.'], 422);
        }

        $scope = implode(':', [
            'idempotency',
            (string) ($request->user()?->getAuthIdentifier() ?? 'guest'),
            (string) ($request->route()?->getName() ?? $request->path()),
            hash('sha256', $idempotencyKey),
        ]);
        $fingerprint = hash('sha256', json_encode([
            'method' => $request->method(),
            'path' => $request->path(),
            'body' => $request->except(['password', 'password_confirmation', 'token']),
        ], JSON_THROW_ON_ERROR));

        $cached = Cache::get($scope);
        if ($cached !== null) {
            return $this->responseFromCache($cached, $fingerprint);
        }

        try {
            return Cache::lock($scope . ':lock', 30)->block(10, function () use ($request, $next, $scope, $fingerprint): Response {
                $cached = Cache::get($scope);
                if ($cached !== null) {
                    return $this->responseFromCache($cached, $fingerprint);
                }

                $response = $next($request);
                if ($response->isSuccessful()) {
                    Cache::put($scope, [
                        'fingerprint' => $fingerprint,
                        'content' => json_decode($response->getContent(), true),
                        'status' => $response->getStatusCode(),
                    ], now()->addSeconds((int) config('cache.idempotency_ttl', self::DEFAULT_TTL_SECONDS)));
                }

                return $response;
            });
        } catch (LockTimeoutException) {
            return response()->json(['message' => 'A request with this Idempotency-Key is already in progress.'], 409);
        }
    }

    private function responseFromCache(array $cached, string $fingerprint): Response
    {
        if (($cached['fingerprint'] ?? null) !== $fingerprint) {
            return response()->json(['message' => 'The Idempotency-Key was already used with different request parameters.'], 422);
        }

        return response()->json($cached['content'] ?? null, (int) ($cached['status'] ?? Response::HTTP_OK))
            ->header('Idempotency-Replayed', 'true');
    }
}
