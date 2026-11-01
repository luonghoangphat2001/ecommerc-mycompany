<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class DhlCarrierAdapter extends BaseCarrierAdapter
{
    private const SANDBOX_URL = 'https://express.api.dhl.com/mydhlapi/test/';
    private const PRODUCTION_URL = 'https://express.api.dhl.com/mydhlapi/';

    public function getId(): string
    {
        return 'dhl';
    }

    public function getName(): string
    {
        return 'DHL Express';
    }

    public function isDomestic(): bool
    {
        return false;
    }

    public function isInternational(): bool
    {
        return true;
    }

    public function isConfigured(): bool
    {
        $config = $this->getConfig();
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }

    protected function getBaseUrl(): string
    {
        return $this->isSandbox() ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    /**
     * @inheritDoc
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình API credentials DHL (API Key / Client Secret còn trống).',
                'details' => ['status' => 'missing_credentials'],
            ];
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
                return [
                    'success' => true,
                    'message' => 'Kết nối DHL Express API thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                    'details' => ['status' => $response->status()],
                ];
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối DHL: ' . ($response->json()['detail'] ?? $response->body()),
                'details' => $response->json() ?? ['status' => $response->status()],
            ];
        } catch (\Exception $e) {
            $this->log('error', 'DHL connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ DHL Express: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsDestination(string $country, ?string $state = null): bool
    {
        // DHL international supports non-VN destinations
        return strtoupper(trim($country)) !== 'VN';
    }

    /**
     * @inheritDoc
     */
    public function getAvailableServices(array $destination, array $package = []): array
    {
        if (!$this->supportsDestination($destination['country'] ?? '')) {
            return [];
        }

        return [
            ['service_id' => 'P', 'service_name' => 'DHL Express Worldwide', 'estimated_days' => 4],
            ['service_id' => 'K', 'service_name' => 'DHL Express 9:00', 'estimated_days' => 2],
        ];
    }

    /**
     * @inheritDoc
     */
    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        if (!$this->supportsDestination($destination['country'] ?? '') || !$this->isConfigured()) {
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
                    'length' => max(5, (int) ($package['length'] ?? 20)),
                    'width' => max(5, (int) ($package['width'] ?? 15)),
                    'height' => max(5, (int) ($package['height'] ?? 10)),
                    'plannedShippingDate' => now()->addDay()->format('Y-m-d'),
                    'isCustomsDeclarable' => 'false',
                    'unitOfMeasurement' => 'metric',
                ]);

            if ($response->successful()) {
                $products = $response->json()['products'] ?? [];
                if (!empty($products)) {
                    $first = $products[0];
                    $totalPrice = (int) ($first['totalPrice'][0]['price'] ?? 0);
                    return [
                        'amount' => $this->applyMarkup($totalPrice),
                        'currency' => $first['totalPrice'][0]['priceCurrency'] ?? 'VND',
                        'service_name' => $first['productName'] ?? 'DHL Express',
                        'estimated_days' => 4,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->log('error', 'DHL calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'DHL credentials not configured'];
        }

        if ($this->isSandbox()) {
            $tracking = 'DHL-' . strtoupper(substr(md5($order->id . time()), 0, 10));
            return [
                'success' => true,
                'tracking_number' => $tracking,
                'reference' => 'SANDBOX-' . $order->number,
                'carrier' => 'dhl',
                'raw' => ['mode' => 'sandbox_simulated', 'tracking' => $tracking],
            ];
        }

        return ['success' => false, 'message' => 'Live shipment creation requires production environment approval'];
    }

    /**
     * @inheritDoc
     */
    public function cancelShipment(string $trackingNumber): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getTracking(string $trackingNumber): array
    {
        return [
            'tracking_number' => $trackingNumber,
            'carrier' => 'dhl',
            'status' => 'transit',
            'status_text' => 'Bưu phẩm đang trên chuyến bay quốc tế',
            'history' => [
                ['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Lô hàng được thông quan và rời trung tâm trung chuyển'],
            ],
        ];
    }
}
