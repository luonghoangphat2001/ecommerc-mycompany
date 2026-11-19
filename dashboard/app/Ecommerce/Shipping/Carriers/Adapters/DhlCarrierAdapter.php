<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class DhlCarrierAdapter extends BaseCarrierAdapter
{
    protected string $carrierId = 'dhl';

    protected string $carrierName = 'DHL Express';

    protected bool $supportsInternational = true;

    private const SANDBOX_URL = 'https://express.api.dhl.com/mydhlapi/test/';

    private const PRODUCTION_URL = 'https://express.api.dhl.com/mydhlapi/';

    public function isConfigured(): bool
    {
        return $this->hasConfigValues('client_id', 'client_secret');
    }

    protected function getBaseUrl(): string
    {
        return $this->environmentUrl(self::SANDBOX_URL, self::PRODUCTION_URL);
    }

    /**
     * {@inheritDoc}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return $this->missingCredentialsResponse('Chưa cấu hình API credentials DHL (API Key / Client Secret còn trống).');
        }

        try {
            $config = $this->getConfig();
            $url = $this->getBaseUrl() . 'rates';

            $response = Http::withBasicAuth((string) $config['client_id'], (string) $config['client_secret'])
                ->timeout(5)
                ->get($url, [
                    'accountNumber' => (string) ($config['shop_id'] ?? '123456789'),
                    'originCountryCode' => 'VN',
                    'originCityName' => 'Ho Chi Minh',
                    'destinationCountryCode' => 'SG',
                    'destinationCityName' => 'Singapore',
                    'weight' => 1,
                    'length' => 10,
                    'width' => 10,
                    'height' => 10,
                    'plannedShippingDate' => now()->addDay()->format('Y-m-d'),
                    'isCustomsDeclarable' => 'false',
                    'unitOfMeasurement' => 'metric',
                ]);

            if ($response->successful()) {
                return $this->successResponse(
                    'Kết nối DHL Express API thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                    ['status' => $response->status()]
                );
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối DHL: ' . ($response->json()['detail'] ?? $response->body()),
                'details' => $response->json() ?? ['status' => $response->status()],
            ];
        } catch (\Exception $e) {
            $this->log('error', 'DHL connection test error: ' . $e->getMessage());

            return $this->exceptionResponse('Không thể kết nối đến máy chủ DHL Express: ' . $e->getMessage(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDestination(string $country, ?string $state = null): bool
    {
        // DHL international supports non-VN destinations
        return strtoupper(trim($country)) !== 'VN';
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableServices(array $destination, array $package = []): array
    {
        if (! $this->supportsDestination($destination['country'] ?? '')) {
            return [];
        }

        return [
            ['service_id' => 'P', 'service_name' => 'DHL Express Worldwide', 'estimated_days' => 4],
            ['service_id' => 'K', 'service_name' => 'DHL Express 9:00', 'estimated_days' => 2],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        if (! $this->canCalculateRate($destination, '')) {
            return null;
        }

        try {
            $config = $this->getConfig();
            $weightKg = max(0.5, round(($package['weight'] ?? 500) / 1000, 2));

            $url = $this->getBaseUrl() . 'rates';
            $response = Http::withBasicAuth((string) $config['client_id'], (string) $config['client_secret'])
                ->timeout(6)
                ->get($url, [
                    'accountNumber' => (string) ($config['shop_id'] ?? '123456789'),
                    'originCountryCode' => 'VN',
                    'originCityName' => 'Ho Chi Minh',
                    'destinationCountryCode' => strtoupper($destination['country']),
                    'destinationCityName' => (string) ($destination['city'] ?? 'Capital'),
                    'weight' => $weightKg,
                    'length' => $this->packageValue($package, 'length', 20, 5),
                    'width' => $this->packageValue($package, 'width', 15, 5),
                    'height' => $this->packageValue($package, 'height', 10, 5),
                    'plannedShippingDate' => now()->addDay()->format('Y-m-d'),
                    'isCustomsDeclarable' => 'false',
                    'unitOfMeasurement' => 'metric',
                ]);

            if ($response->successful()) {
                $products = $response->json()['products'] ?? [];
                if (! empty($products)) {
                    $first = $products[0];
                    $totalPrice = (int) ($first['totalPrice'][0]['price'] ?? 0);

                    return $this->rateResponse(
                        $this->applyMarkup($totalPrice),
                        $first['productName'] ?? 'DHL Express',
                        4,
                        $first['totalPrice'][0]['priceCurrency'] ?? 'VND'
                    );
                }
            }
        } catch (\Exception $e) {
            $this->log('error', 'DHL calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return $this->credentialsMissing('DHL');
        }

        if ($this->isSandbox()) {
            $tracking = 'DHL-' . strtoupper(substr(md5($order->id . time()), 0, 10));

            return $this->sandboxShipmentResponse($order, $tracking);
        }

        return $this->liveShipmentApprovalRequired();
    }

    /**
     * {@inheritDoc}
     */
    public function cancelShipment(string $trackingNumber): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getTracking(string $trackingNumber): array
    {
        return $this->trackingResponse(
            $trackingNumber,
            'transit',
            'Bưu phẩm đang trên chuyến bay quốc tế',
            [['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Lô hàng được thông quan và rời trung tâm trung chuyển']]
        );
    }
}
