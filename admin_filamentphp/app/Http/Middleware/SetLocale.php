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
        // Lấy locale từ query parameter (ưu tiên 'lang' trước, sau đó 'locale'), mặc định là 'en'
        $locale = $request->query('lang', $request->query('locale', $request->header('Accept-Language')));

        // Nếu không có locale hoặc locale không hợp lệ, sử dụng locale mặc định
        if (!$locale || !in_array($locale, ['en', 'vi'])) {
            $locale = config('app.locale');
        }

        // Set locale cho ứng dụng
        app()->setLocale($locale);

        return $next($request);
    }
}
