<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSettingEnabled
{
    public function __construct(private readonly Container $container) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $settingClass, string $property): Response
    {
        $className = 'App\\Settings\\' . $settingClass;

        if (! class_exists($className)) {
            abort(500, "Setting class [{$className}] not found.");
        }

        $settings = $this->container->make($className);

        if (! isset($settings->$property) || ! $settings->$property) {
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
