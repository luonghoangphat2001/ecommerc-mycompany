<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CheckTokenExpiration
{
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

        // Kiểm tra token trong database
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Kiểm tra thời gian tạo token (15 phút)
        $createdAt = Carbon::parse($accessToken->created_at);
        if ($createdAt->diffInMinutes(Carbon::now()) > 15) {
            $accessToken->delete(); // Xóa token hết hạn
            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }
}
