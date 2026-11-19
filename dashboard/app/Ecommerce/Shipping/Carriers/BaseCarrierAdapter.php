<?php

namespace App\Ecommerce\Shipping\Carriers;

use App\Ecommerce\Shipping\Carriers\Contracts\CarrierAdapterInterface;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseCarrierAdapter implements CarrierAdapterInterface
{
    protected ?array $cachedConfig = null;

    protected string $carrierId = '';

    protected string $carrierName = '';

    protected bool $supportsDomestic = false;

    protected bool $supportsInternational = false;

    public function getId(): string
    {
        return $this->carrierId;
    }

    public function getName(): string
    {
        return $this->carrierName;
    }

    public function isDomestic(): bool
    {
        return $this->supportsDomestic;
    }

    public function isInternational(): bool
    {
        return $this->supportsInternational;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        if ($this->cachedConfig !== null) {
            return $this->cachedConfig;
        }

        $key = $this->configKey();
        $record = DB::table('shop_settings')->where('key', $key)->first();

        $dbValues = [];
        if ($record && $record->value) {
            $dbValues = $this->decodeConfigValue($record->value);
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

        $key = $this->configKey();
        DB::table('shop_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => json_encode($merged, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]
        );

        $this->cachedConfig = $merged;
    }

    protected function configKey(): string
    {
        return 'carrier_' . $this->getId();
    }

    protected function decodeConfigValue(mixed $value): array
    {
        return is_string($value) ? (json_decode($value, true) ?: []) : (array) $value;
    }

    protected function configValue(string $key, mixed $default = null): mixed
    {
        return $this->getConfig()[$key] ?? $default;
    }

    protected function packageValue(array $package, string $key, int $default, int $minimum = 0): int
    {
        return max($minimum, (int) ($package[$key] ?? $default));
    }

    protected function packageDimensions(array $package): array
    {
        return [
            'weight' => $this->packageValue($package, 'weight', 500, 100),
            'length' => $this->packageValue($package, 'length', 20, 5),
            'width' => $this->packageValue($package, 'width', 15, 5),
            'height' => $this->packageValue($package, 'height', 10, 5),
        ];
    }

    /**
     * Default carrier configuration blueprint.
     */
    protected function getDefaultConfig(): array
    {
        return [
            'enabled' => false,
            'sandbox' => true,
            'api_token' => $this->envValue('API_TOKEN'),
            'shop_id' => $this->envValue('SHOP_ID'),
            'client_id' => $this->envValue('CLIENT_ID'),
            'client_secret' => $this->envValue('CLIENT_SECRET'),
            'markup_amount' => 0,
            'markup_percent' => 0,
            'fallback_rate' => 35000,
            'supported_zones' => ['*'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(): bool
    {
        return (bool) $this->configValue('enabled', false);
    }

    /**
     * {@inheritDoc}
     */
    public function isSandbox(): bool
    {
        return (bool) $this->configValue('sandbox', true);
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

    protected function canCalculateRate(array $destination, string $defaultCountry = 'VN'): bool
    {
        return $this->supportsDestination($destination['country'] ?? $defaultCountry) && $this->isConfigured();
    }

    protected function hasConfigValues(string ...$keys): bool
    {
        $config = $this->getConfig();

        foreach ($keys as $key) {
            if (empty($config[$key])) {
                return false;
            }
        }

        return true;
    }

    protected function jsonTokenHeaders(array $additional = []): array
    {
        return array_merge(
            ['Token' => (string) $this->configValue('api_token', '')],
            $additional,
            ['Content-Type' => 'application/json', 'Accept' => 'application/json']
        );
    }

    protected function environmentUrl(string $sandboxUrl, string $productionUrl): string
    {
        return $this->isSandbox() ? $sandboxUrl : $productionUrl;
    }

    protected function envValue(string $suffix): string
    {
        return (string) env(strtoupper($this->getId()) . '_' . $suffix, '');
    }

    protected function failure(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }

    protected function successResponse(string $message, array $details = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'details' => $details,
        ];
    }

    protected function credentialsMissing(string $carrier): array
    {
        return $this->failure($carrier . ' credentials not configured');
    }

    protected function missingCredentialsResponse(string $message): array
    {
        return [
            ...$this->failure($message),
            'details' => ['status' => 'missing_credentials'],
        ];
    }

    protected function exceptionResponse(string $message, \Throwable $exception): array
    {
        return [
            ...$this->failure($message),
            'details' => ['exception' => $exception->getMessage()],
        ];
    }

    protected function failureWithRaw(string $message, mixed $raw): array
    {
        return [
            ...$this->failure($message),
            'raw' => $raw,
        ];
    }

    protected function trackingResponse(string $trackingNumber, string $status, string $statusText, array $history = []): array
    {
        return [
            'tracking_number' => $trackingNumber,
            'carrier' => $this->getId(),
            'status' => $status,
            'status_text' => $statusText,
            'history' => $history,
        ];
    }

    protected function shipmentResponse(string $trackingNumber, ?string $reference = null, array $raw = []): array
    {
        $response = [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'carrier' => $this->getId(),
            'raw' => $raw,
        ];

        if ($reference !== null) {
            $response['reference'] = $reference;
        }

        return $response;
    }

    protected function liveShipmentApprovalRequired(): array
    {
        return $this->failure('Live shipment creation requires production environment approval');
    }

    protected function unknownTracking(string $trackingNumber): array
    {
        return $this->trackingResponse($trackingNumber, 'unknown', 'Không tìm thấy thông tin vận đơn');
    }

    protected function rateResponse(int $amount, string $serviceName, int $estimatedDays, string $currency = 'VND'): array
    {
        return [
            'amount' => $amount,
            'currency' => $currency,
            'service_name' => $serviceName,
            'estimated_days' => $estimatedDays,
        ];
    }

    protected function sandboxShipmentResponse(Order $order, string $tracking, ?string $referencePrefix = 'SANDBOX-', bool $includeTrackingInRaw = true): array
    {
        $raw = ['mode' => 'sandbox_simulated'];

        if ($includeTrackingInRaw) {
            $raw['tracking'] = $tracking;
        }

        return $this->shipmentResponse(
            $tracking,
            $referencePrefix !== null ? $referencePrefix . $order->number : null,
            $raw
        );
    }

    /**
     * Mask secret token for display in UI.
     */
    public function getMaskedToken(string $key = 'api_token'): string
    {
        $val = (string) $this->configValue($key, '');
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
