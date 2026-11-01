<?php

namespace App\Ecommerce\Shipping\Carriers;

use App\Ecommerce\Shipping\Carriers\Contracts\CarrierAdapterInterface;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseCarrierAdapter implements CarrierAdapterInterface
{
    protected ?array $cachedConfig = null;

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        if ($this->cachedConfig !== null) {
            return $this->cachedConfig;
        }

        $key = 'carrier_' . $this->getId();
        $record = DB::table('shop_settings')->where('key', $key)->first();

        $dbValues = [];
        if ($record && $record->value) {
            $dbValues = is_string($record->value) ? (json_decode($record->value, true) ?: []) : (array) $record->value;
        }

        $defaultConfig = $this->getDefaultConfig();
        $this->cachedConfig = array_merge($defaultConfig, $dbValues);

        return $this->cachedConfig;
    }

    /**
     * Save configuration into database.
     */
    public function saveConfig(array $data): void
    {
        $current = $this->getConfig();
        $merged = array_merge($current, $data);

        $key = 'carrier_' . $this->getId();
        DB::table('shop_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => json_encode($merged, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]
        );

        $this->cachedConfig = $merged;
    }

    /**
     * Default carrier configuration blueprint.
     */
    protected function getDefaultConfig(): array
    {
        return [
            'enabled' => false,
            'sandbox' => true,
            'api_token' => env(strtoupper($this->getId()) . '_API_TOKEN', ''),
            'shop_id' => env(strtoupper($this->getId()) . '_SHOP_ID', ''),
            'client_id' => env(strtoupper($this->getId()) . '_CLIENT_ID', ''),
            'client_secret' => env(strtoupper($this->getId()) . '_CLIENT_SECRET', ''),
            'markup_amount' => 0,
            'markup_percent' => 0,
            'fallback_rate' => 35000,
            'supported_zones' => ['*'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->getConfig()['enabled'] ?? false);
    }

    /**
     * @inheritDoc
     */
    public function isSandbox(): bool
    {
        return (bool) ($this->getConfig()['sandbox'] ?? true);
    }

    /**
     * Apply admin markup to calculated rate.
     */
    protected function applyMarkup(int $rate): int
    {
        $config = $this->getConfig();
        $amount = (int) ($config['markup_amount'] ?? 0);
        $percent = (float) ($config['markup_percent'] ?? 0);

        $withPercent = $percent > 0 ? (int) round($rate * (1 + ($percent / 100))) : $rate;
        $total = $withPercent + $amount;

        return max(0, $total);
    }

    /**
     * Mask secret token for display in UI.
     */
    public function getMaskedToken(string $key = 'api_token'): string
    {
        $val = (string) ($this->getConfig()[$key] ?? '');
        if (strlen($val) <= 6) {
            return $val !== '' ? '••••••' : '';
        }

        return substr($val, 0, 3) . '••••••' . substr($val, -3);
    }

    /**
     * Log shipping event safely.
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        try {
            Log::channel('shipping')->{$level}("[Carrier:{$this->getId()}] " . $message, $context);
        } catch (\Exception $e) {
            Log::{$level}("[Carrier:{$this->getId()}] " . $message, $context);
        }
    }
}
