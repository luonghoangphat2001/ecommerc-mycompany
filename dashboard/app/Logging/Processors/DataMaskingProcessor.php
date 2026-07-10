<?php

namespace App\Logging\Processors;

use Monolog\LogRecord;

class DataMaskingProcessor
{
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'access_token',
        'refresh_token',
        'authorization',
        'smtp_password',
        'api_secret',
        'webhook_secret',
        'cvv',
        'card_number',
        'payment_token',
    ];

    protected array $partialMaskFields = [
        'email',
        'phone',
        'address',
    ];

    public function __invoke(\Illuminate\Log\Logger $logger)
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
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->maskData($value);
                continue;
            }

            if (is_string($key)) {
                $lowerKey = strtolower($key);

                // Full mask for sensitive fields
                foreach ($this->sensitiveFields as $field) {
                    if (str_contains($lowerKey, $field)) {
                        $data[$key] = '********';
                        break;
                    }
                }

                // Partial mask for personal info (only if not already fully masked)
                if ($data[$key] !== '********') {
                    foreach ($this->partialMaskFields as $field) {
                        if (str_contains($lowerKey, $field) && is_string($value)) {
                            $data[$key] = $this->partialMask($value, $field);
                            break;
                        }
                    }
                }
            }
        }

        return $data;
    }

    protected function partialMask(string $value, string $type): string
    {
        if (empty($value)) return $value;

        if ($type === 'email' && str_contains($value, '@')) {
            $parts = explode('@', $value);
            $name = $parts[0];
            $maskedName = substr($name, 0, 1) . str_repeat('*', max(1, strlen($name) - 2)) . substr($name, -1);
            if (strlen($name) <= 2) {
                $maskedName = substr($name, 0, 1) . '***';
            }
            return $maskedName . '@' . $parts[1];
        }

        if ($type === 'phone' && strlen($value) > 4) {
            return substr($value, 0, 3) . str_repeat('*', strlen($value) - 6) . substr($value, -3);
        }

        // Default partial mask (e.g., for address)
        if (strlen($value) > 10) {
            return substr($value, 0, 5) . '...' . substr($value, -5);
        }

        return '***';
    }
}
