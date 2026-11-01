<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class GhtkCarrierAdapter extends BaseCarrierAdapter
{
    private const SANDBOX_URL = 'https://services.ghtklab.com/';
    private const PRODUCTION_URL = 'https://services.giaohangtietkiem.vn/';

    public function getId(): string
    {
        return 'ghtk';
    }

    public function getName(): string
    {
        return 'Giao Hàng Tiết Kiệm (GHTK)';
    }

    public function isDomestic(): bool
    {
        return true;
    }

    public function isInternational(): bool
    {
        return false;
    }

    public function isConfigured(): bool
    {
        $config = $this->getConfig();
        return !empty($config['api_token']);
    }

    protected function getBaseUrl(): string
    {
        return $this->isSandbox() ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    protected function getHeaders(): array
    {
        $config = $this->getConfig();
        return [
            'Token' => (string) ($config['api_token'] ?? ''),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * @inheritDoc
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình API credentials (API Token còn trống).',
                'details' => ['status' => 'missing_credentials'],
            ];
        }

        try {
            // Test connection using GHTK fee query with standard sample test params
            $url = $this->getBaseUrl() . 'services/shipment/fee';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url, [
                'pick_province' => 'Hà Nội',
                'pick_district' => 'Quận Cầu Giấy',
                'province' => 'Hà Nội',
                'district' => 'Quận Đống Đa',
                'weight' => 500,
                'value' => 100000,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (($json['success'] ?? false) === true) {
                    return [
                        'success' => true,
                        'message' => 'Kết nối API GHTK thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                        'details' => $json,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối GHTK: ' . ($response->json()['message'] ?? $response->body()),
                'details' => $response->json() ?? ['raw' => $response->body()],
            ];
        } catch (\Exception $e) {
            $this->log('error', 'GHTK connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ GHTK: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsDestination(string $country, ?string $state = null): bool
    {
        return strtoupper(trim($country)) === 'VN';
    }

    /**
     * @inheritDoc
     */
    public function getAvailableServices(array $destination, array $package = []): array
    {
        if (!$this->supportsDestination($destination['country'] ?? 'VN')) {
            return [];
        }

        return [
            ['service_id' => 'road', 'service_name' => 'GHTK Đường bộ', 'transport' => 'road'],
            ['service_id' => 'fly', 'service_name' => 'GHTK Đường hàng không (Nhanh)', 'transport' => 'fly'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        if (!$this->supportsDestination($destination['country'] ?? 'VN') || !$this->isConfigured()) {
            return null;
        }

        try {
            $weight = max(100, (int) ($package['weight'] ?? 500));
            $url = $this->getBaseUrl() . 'services/shipment/fee';

            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url, [
                'pick_province' => (string) ($this->getConfig()['sender_province'] ?? 'Hồ Chí Minh'),
                'pick_district' => (string) ($this->getConfig()['sender_district'] ?? 'Quận 1'),
                'province' => (string) ($destination['state'] ?? 'Hồ Chí Minh'),
                'district' => (string) ($destination['city'] ?? 'Quận 1'),
                'address' => (string) ($destination['address_detail'] ?? ''),
                'weight' => $weight,
                'value' => (int) ($destination['subtotal'] ?? 100000),
                'transport' => $serviceCode === 'fly' ? 'fly' : 'road',
            ]);

            if ($response->successful() && ($response->json()['success'] ?? false) === true) {
                $fee = (int) ($response->json()['fee']['fee'] ?? 0);
                return [
                    'amount' => $this->applyMarkup($fee),
                    'currency' => 'VND',
                    'service_name' => $serviceCode === 'fly' ? 'GHTK Hàng không' : 'GHTK Tiêu chuẩn',
                    'estimated_days' => $serviceCode === 'fly' ? 2 : 3,
                ];
            }
        } catch (\Exception $e) {
            $this->log('error', 'GHTK calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'GHTK API credentials not configured'];
        }

        if ($this->isSandbox() && empty($options['force_real_api'])) {
            $tracking = 'GHTK-TEST-' . strtoupper(substr(md5($order->id . time()), 0, 8));
            return [
                'success' => true,
                'tracking_number' => $tracking,
                'reference' => 'SANDBOX-' . $order->number,
                'carrier' => 'ghtk',
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
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $url = $this->getBaseUrl() . 'services/shipment/cancel/' . $trackingNumber;
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url);
            return $response->successful() && ($response->json()['success'] ?? false) === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getTracking(string $trackingNumber): array
    {
        if (!$this->isConfigured() || str_starts_with($trackingNumber, 'GHTK-TEST-')) {
            return [
                'tracking_number' => $trackingNumber,
                'carrier' => 'ghtk',
                'status' => 'processing',
                'status_text' => 'GHTK đang xử lý đơn hàng',
                'history' => [
                    ['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Tiếp nhận đơn hàng thành công'],
                ],
            ];
        }

        try {
            $url = $this->getBaseUrl() . 'services/shipment/v2/' . $trackingNumber;
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url);

            if ($response->successful() && ($response->json()['success'] ?? false) === true) {
                $orderData = $response->json()['order'] ?? [];
                return [
                    'tracking_number' => $trackingNumber,
                    'carrier' => 'ghtk',
                    'status' => (string) ($orderData['status'] ?? 'unknown'),
                    'status_text' => (string) ($orderData['status_text'] ?? 'Đang giao hàng'),
                    'history' => $orderData['history'] ?? [],
                ];
            }
        } catch (\Exception $e) {
            $this->log('error', 'GHTK tracking error: ' . $e->getMessage());
        }

        return [
            'tracking_number' => $trackingNumber,
            'carrier' => 'ghtk',
            'status' => 'unknown',
            'status_text' => 'Không tìm thấy thông tin vận đơn',
            'history' => [],
        ];
    }
}
