<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\ApiLogger;

class ApiLogMiddleware
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Only log API requests
        if (!$request->is('api/*')) {
            return $next($request);
        }
        
        // Log request
        ApiLogger::logRequest(
            $request->method(),
            $request->path(),
            $request->headers->all(),
            $request->all()
        );
        
        $response = $next($request);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        // Log response
        $responseBody = null;
        if (method_exists($response, 'getContent')) {
            $responseBody = json_decode($response->getContent(), true);
        }
        
        ApiLogger::logResponse(
            $response->getStatusCode(),
            $responseBody,
            $duration
        );
        
        return $response;
    }
}
