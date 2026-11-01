<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class ViettelPostCarrierAdapter extends BaseCarrierAdapter
{
    private const BASE_URL = 'https://partner.viettelpost.vn/v2/';

    public function getId(): string
    {
        return 'viettel_post';
    }

    public function getName(): string
    {
        return 'Viettel Post';
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
            $url = self::BASE_URL . 'categories/listService';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url, ['TYPE' => 1]);

            if ($response->successful()) {
                $json = $response->json();
                if (($json['status'] ?? 0) === 200) {
                    return [
                        'success' => true,
                        'message' => 'Kết nối API Viettel Post thành công.',
                        'details' => [
                            'status' => 200,
                            'services_count' => count($json['data'] ?? []),
                        ],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối Viettel Post: ' . ($response->json()['message'] ?? $response->body()),
                'details' => $response->json() ?? ['raw' => $response->body()],
            ];
        } catch (\Exception $e) {
            $this->log('error', 'Viettel Post connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ Viettel Post: ' . $e->getMessage(),
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
            ['service_id' => 'VCN', 'service_name' => 'Chuyển phát nhanh (VCN)', 'estimated_days' => 2],
            ['service_id' => 'VTK', 'service_name' => 'Chuyển phát tiết kiệm (VTK)', 'estimated_days' => 4],
            ['service_id' => 'VHT', 'service_name' => 'Chuyển phát hỏa tốc (VHT)', 'estimated_days' => 1],
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
            $url = self::BASE_URL . 'order/getPrice';

            $payload = [
                'PRODUCT_WEIGHT' => $weight,
                'PRODUCT_PRICE' => (int) ($destination['subtotal'] ?? 100000),
                'MONEY_COLLECTION' => 0,
                'ORDER_SERVICE' => $serviceCode ?: 'VCN',
                'SENDER_PROVINCE' => (int) ($this->getConfig()['sender_province_id'] ?? 1),
                'SENDER_DISTRICT' => (int) ($this->getConfig()['sender_district_id'] ?? 1),
                'RECEIVER_PROVINCE' => (int) ($destination['province_id'] ?? 1),
                'RECEIVER_DISTRICT' => (int) ($destination['district_id'] ?? 1),
                'PRODUCT_TYPE' => 'HH',
                'TYPE' => 1,
            ];

            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, $payload);
            if ($response->successful() && ($response->json()['status'] ?? 0) === 200) {
                $fee = (int) ($response->json()['data']['MONEY_TOTAL'] ?? 0);
                return [
                    'amount' => $this->applyMarkup($fee),
                    'currency' => 'VND',
                    'service_name' => 'Viettel Post ' . $serviceCode,
                    'estimated_days' => 2,
                ];
            }
        } catch (\Exception $e) {
            $this->log('error', 'Viettel Post calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Viettel Post API credentials not configured'];
        }

        if ($this->isSandbox() && empty($options['force_real_api'])) {
            $tracking = 'VT-' . strtoupper(substr(md5($order->id . time()), 0, 9));
            return [
                'success' => true,
                'tracking_number' => $tracking,
                'reference' => 'SANDBOX-' . $order->number,
                'carrier' => 'viettel_post',
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
            'carrier' => 'viettel_post',
            'status' => 'delivering',
            'status_text' => 'Bưu cục Viettel Post đang chuyển tiếp hàng',
            'history' => [
                ['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Nhận bưu phẩm tại điểm giao dịch'],
            ],
        ];
    }
}
