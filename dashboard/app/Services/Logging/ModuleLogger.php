<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;

class ModuleLogger
{
    protected string $module;

    public function __construct(string $module = 'system')
    {
        $this->module = $module;
    }

    public static function module(string $module): self
    {
        return new self($module);
    }

    // Static helpers for specific modules
    public static function order(): self { return new self('order'); }
    public static function product(): self { return new self('product'); }
    public static function post(): self { return new self('post'); }
    public static function user(): self { return new self('user'); }
    public static function payment(): self { return new self('payment'); }
    public static function refund(): self { return new self('refund'); }
    public static function shipping(): self { return new self('shipping'); }
    public static function webhook(): self { return new self('webhook'); }
    public static function auth(): self { return new self('auth'); }
    public static function system(): self { return new self('system'); }

    public function log(string $level, string $action, string $message, array $context = []): void
    {
        $context['module'] = $this->module;
        $context['action'] = $action;

        // Ensure the channel exists, fallback to daily if not
        $channel = in_array($this->module, array_keys(config('logging.channels'))) 
            ? $this->module 
            : 'daily';

        Log::channel($channel)->log($level, $message, $context);
    }

    public function debug(string $action, string $message, array $context = []): void
    {
        $this->log('debug', $action, $message, $context);
    }

    public function info(string $action, string $message, array $context = []): void
    {
        $this->log('info', $action, $message, $context);
    }

    public function notice(string $action, string $message, array $context = []): void
    {
        $this->log('notice', $action, $message, $context);
    }

    public function warning(string $action, string $message, array $context = []): void
    {
        $this->log('warning', $action, $message, $context);
    }

    public function error(string $action, string $message, array $context = []): void
    {
        $this->log('error', $action, $message, $context);
    }

    public function critical(string $action, string $message, array $context = []): void
    {
        $this->log('critical', $action, $message, $context);
    }

    public function alert(string $action, string $message, array $context = []): void
    {
        $this->log('alert', $action, $message, $context);
    }

    public function emergency(string $action, string $message, array $context = []): void
    {
        $this->log('emergency', $action, $message, $context);
    }
}
