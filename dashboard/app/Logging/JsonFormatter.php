<?php

namespace App\Logging;

use Carbon\Carbon;
use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;
use Monolog\LogRecord;

class JsonFormatter extends BaseJsonFormatter
{
    public function format(LogRecord $record): string
    {
        $context = $record->context;
        
        $normalized = [
            'module' => $context['module'] ?? 'system',
            'action' => $context['action'] ?? 'unknown',
            'level' => strtolower($record->level->getName()),
            'message' => $record->message,
            'request_id' => $record->extra['request_id'] ?? null,
            'user_id' => $context['user_id'] ?? auth()->id() ?? null,
            'route' => request()->path() ?? null,
            'method' => request()->method() ?? null,
            'ip' => request()->ip() ?? null,
        ];

        // Specific IDs based on module
        $moduleIds = ['order_id', 'product_id', 'post_id', 'payment_id', 'refund_id', 'transaction_id'];
        foreach ($moduleIds as $idKey) {
            if (isset($context[$idKey])) {
                $normalized[$idKey] = $context[$idKey];
            }
        }

        if (isset($context['old_data'])) {
            $normalized['old_data'] = $context['old_data'];
        }
        
        if (isset($context['new_data'])) {
            $normalized['new_data'] = $context['new_data'];
        }

        // Add any remaining context data that wasn't explicitly handled, except reserved keys
        $reservedKeys = array_merge(['module', 'action', 'old_data', 'new_data', 'user_id'], $moduleIds);
        $additionalData = array_diff_key($context, array_flip($reservedKeys));
        
        if (!empty($additionalData)) {
            $normalized['context'] = $additionalData;
        }

        $normalized['created_at'] = Carbon::now()->format('Y-m-d H:i:s');

        return $this->toJson($normalized, true) . ($this->appendNewline ? "\n" : '');
    }
}
