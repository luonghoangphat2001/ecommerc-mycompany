<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSettingEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $settingClass, string $property): Response
    {
        $className = "App\\Settings\\" . $settingClass;

        if (!class_exists($className)) {
            abort(500, "Setting class [{$className}] not found.");
        }

        $settings = app($className);

        if (!isset($settings->$property) || !$settings->$property) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service is currently unavailable due to system settings.',
                ], 503);
            }
            abort(403, "The requested module or setting [{$property}] is currently disabled.");
        }

        return $next($request);
    }
}
