<?php

namespace App\Logging\Processors;

use Monolog\LogRecord;
use Illuminate\Support\Str;

class RequestIdProcessor
{
    public function __invoke(\Illuminate\Log\Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function (LogRecord $record) {
                $requestId = request()->attributes->get('request_id');

                if (!$requestId) {
                    $requestId = (string) Str::uuid();
                    request()->attributes->set('request_id', $requestId);
                }

                $extra = $record->extra;
                $extra['request_id'] = $requestId;

                return $record->with(extra: $extra);
            });
        }
    }
}
