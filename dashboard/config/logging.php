<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'flare'],
            'ignore_exceptions' => false,
        ],

        'flare' => [
            'driver' => 'flare',
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        // Module Logs
        'order' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/orders/order.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'product' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/products/product.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'post' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/posts/post.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'user' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/users/user.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'payment' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/payments/payment.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'refund' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/refunds/refund.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'shipping' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/shipping/shipping.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'webhook' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/webhooks/webhook.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/auth/auth.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],
        'system' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/system/system.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'formatter' => \App\Logging\JsonFormatter::class,
            'tap' => [\App\Logging\Processors\RequestIdProcessor::class, \App\Logging\Processors\DataMaskingProcessor::class],
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
