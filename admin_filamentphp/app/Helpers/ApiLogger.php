<?php

namespace App\Helpers;

class ApiLogger
{
    /**
     * Log API request/response to daily log file
     */
    public static function log($message, $context = [], $level = 'info')
    {
        $logPath = storage_path('logs/api');
        
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $date = date('Y-m-d');
        $logFile = $logPath . "/api-{$date}.log";
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log API request
     */
    public static function logRequest($method, $path, $headers = [], $body = null)
    {
        $context = [
            'method' => $method,
            'path' => $path,
            'headers' => $headers,
            'body' => $body,
        ];
        
        self::log("API Request", $context);
    }
    
    /**
     * Log API response
     */
    public static function logResponse($statusCode, $body = null, $duration = null)
    {
        $context = [
            'status_code' => $statusCode,
            'body' => $body,
            'duration_ms' => $duration,
        ];
        
        $level = $statusCode >= 400 ? 'error' : 'info';
        self::log("API Response", $context, $level);
    }
}
