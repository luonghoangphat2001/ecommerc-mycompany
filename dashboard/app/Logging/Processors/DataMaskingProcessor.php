<?php

namespace App\Logging\Processors;

use App\Support\Infrastructure;
use Illuminate\Log\Logger;
use Monolog\LogRecord;

class DataMaskingProcessor
{
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function (LogRecord $record) {
                $context = $this->maskData($record->context);

                return $record->with(context: $context);
            });
        }
    }

    protected function maskData(array $data): array
    {
        return Infrastructure::mask($data);
    }
}
