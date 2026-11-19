<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

final class Infrastructure
{
    private const FULL_MASK_FIELDS = ['password', 'password_confirmation', 'access_token', 'refresh_token', 'authorization', 'smtp_password', 'api_secret', 'webhook_secret', 'cvv', 'card_number', 'payment_token'];

    private const PARTIAL_MASK_FIELDS = ['email', 'phone', 'address', 'first_name', 'last_name', 'customer_name'];

    public static function mask(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::mask($value);

                continue;
            }
            if (! is_string($key)) {
                continue;
            }
            $lowerKey = strtolower($key);
            if (self::matches($lowerKey, self::FULL_MASK_FIELDS)) {
                $data[$key] = '********';

                continue;
            }
            if (is_string($value)) {
                foreach (self::PARTIAL_MASK_FIELDS as $field) {
                    if (str_contains($lowerKey, $field)) {
                        $data[$key] = self::partialMask($value, $field);

                        break;
                    }
                }
            }
        }

        return $data;
    }

    public static function customerEmail(Order $order): ?string
    {
        return $order->billingAddress?->email ?? $order->shippingAddress?->email ?? $order->user?->email;
    }

    public static function adminEmail(): ?string
    {
        return config('mail.from.address');
    }

    public static function flushStorefrontCache(): void
    {
        foreach (config('app.supported_locales', ['vi', 'en']) as $localeConfig) {
            $locale = is_array($localeConfig) ? ($localeConfig['code'] ?? 'vi') : (string) $localeConfig;
            Cache::forget("storefront_settings_v1_{$locale}");
            Cache::forget("storefront_settings_v2_{$locale}");
        }
        Cache::forget('all_menus');
    }

    private static function matches(string $key, array $fields): bool
    {
        foreach ($fields as $field) {
            if (str_contains($key, $field)) {
                return true;
            }
        }

        return false;
    }

    private static function partialMask(string $value, string $type): string
    {
        if ($value === '') {
            return $value;
        }
        if ($type === 'email' && str_contains($value, '@')) {
            [$name, $domain] = explode('@', $value, 2);

            return (strlen($name) <= 2 ? substr($name, 0, 1) . '***' : substr($name, 0, 1) . str_repeat('*', max(1, strlen($name) - 2)) . substr($name, -1)) . '@' . $domain;
        }
        if ($type === 'phone' && strlen($value) > 4) {
            return substr($value, 0, 3) . str_repeat('*', max(1, strlen($value) - 6)) . substr($value, -3);
        }

        return strlen($value) > 10 ? substr($value, 0, 5) . '...' . substr($value, -5) : '***';
    }
}
