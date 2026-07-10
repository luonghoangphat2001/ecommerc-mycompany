<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CheckTokenExpiration
{
    private const ACCESS_TOKEN_TTL_SECONDS = 900;
    private const REFRESH_TOKEN_TTL_SECONDS = 604800;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $createdAt = Carbon::parse($accessToken->created_at);
        $tokenAgeInSeconds = $createdAt->diffInSeconds(Carbon::now());

        if ($tokenAgeInSeconds > self::REFRESH_TOKEN_TTL_SECONDS) {
            $accessToken->delete();

            return response()->json(['message' => 'Token expired'], 401);
        }

        if ($tokenAgeInSeconds > self::ACCESS_TOKEN_TTL_SECONDS) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }
}
