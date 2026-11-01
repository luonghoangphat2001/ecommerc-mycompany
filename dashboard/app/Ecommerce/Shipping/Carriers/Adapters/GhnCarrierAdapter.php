<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class GhnCarrierAdapter extends BaseCarrierAdapter
{
    private const SANDBOX_URL = 'https://dev-online-gateway.ghn.vn/shiip/public-api/';
    private const PRODUCTION_URL = 'https://online-gateway.ghn.vn/shiip/public-api/';

    public function getId(): string
    {
        return 'ghn';
    }

    public function getName(): string
    {
        return 'Giao Hàng Nhanh (GHN)';
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
        return !empty($config['api_token']) && !empty($config['shop_id']);
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
            'ShopId' => (string) ($config['shop_id'] ?? ''),
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
                'message' => 'Chưa cấu hình API credentials (API Token hoặc Shop ID còn trống).',
                'details' => ['status' => 'missing_credentials'],
            ];
        }

        try {
            $url = $this->getBaseUrl() . 'master-data/province';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                return [
                    'success' => true,
                    'message' => 'Kết nối API GHN thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                    'details' => [
                        'code' => $response->json()['code'],
                        'mode' => $this->isSandbox() ? 'sandbox' : 'production',
                        'data_sample' => count($response->json()['data'] ?? []) . ' provinces available',
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối GHN: ' . ($response->json()['message'] ?? $response->body()),
                'details' => $response->json() ?? ['raw' => $response->body()],
            ];
        } catch (\Exception $e) {
            $this->log('error', 'Connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ GHN: ' . $e->getMessage(),
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

        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $fromDistrict = (int) ($this->getConfig()['sender_district_id'] ?? 1442); // Default Quận 1 HCM
            $toDistrict = (int) ($destination['district_id'] ?? 1444);

            $url = $this->getBaseUrl() . 'v2/shipping-order/available-services';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, [
                'shop_id' => (int) ($this->getConfig()['shop_id'] ?? 0),
                'from_district' => $fromDistrict,
                'to_district' => $toDistrict,
            ]);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                return collect($response->json()['data'] ?? [])->map(function ($s) {
                    return [
                        'service_id' => (string) $s['service_id'],
                        'service_name' => $s['short_name'] ?? 'GHN ' . $s['service_type_id'],
                        'service_type_id' => $s['service_type_id'] ?? 2,
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            $this->log('warning', 'Failed to fetch GHN available services: ' . $e->getMessage());
        }

        return [
            ['service_id' => '53320', 'service_name' => 'GHN Chuẩn', 'service_type_id' => 2],
            ['service_id' => '53321', 'service_name' => 'GHN Nhanh', 'service_type_id' => 1],
        ];
    }

    /**
     * @inheritDoc
     */
    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        if (!$this->supportsDestination($destination['country'] ?? 'VN')) {
            return null;
        }

        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $weight = max(100, (int) ($package['weight'] ?? 500));
            $length = max(5, (int) ($package['length'] ?? 20));
            $width = max(5, (int) ($package['width'] ?? 15));
            $height = max(5, (int) ($package['height'] ?? 10));

            $payload = [
                'service_id' => is_numeric($serviceCode) ? (int) $serviceCode : 53320,
                'service_type_id' => 2,
                'to_district_id' => (int) ($destination['district_id'] ?? 1444),
                'to_ward_code' => (string) ($destination['ward_code'] ?? '20101'),
                'weight' => $weight,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'insurance_value' => min(5000000, (int) ($destination['subtotal'] ?? 0)),
            ];

            $url = $this->getBaseUrl() . 'v2/shipping-order/fee';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, $payload);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                $totalFee = (int) ($response->json()['data']['total'] ?? 0);
                $finalAmount = $this->applyMarkup($totalFee);

                return [
                    'amount' => $finalAmount,
                    'currency' => 'VND',
                    'service_name' => 'GHN Tiêu chuẩn',
                    'estimated_days' => 2,
                ];
            }

            $this->log('warning', 'GHN fee API returned error: ' . $response->body());
        } catch (\Exception $e) {
            $this->log('error', 'GHN calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'GHN API credentials not configured'];
        }

        if ($this->isSandbox() && empty($options['force_real_api'])) {
            $tracking = 'GHN-TEST-' . strtoupper(substr(md5($order->id . time()), 0, 8));
            return [
                'success' => true,
                'tracking_number' => $tracking,
                'reference' => 'SANDBOX-' . $order->number,
                'carrier' => 'ghn',
                'raw' => ['mode' => 'sandbox_simulated', 'tracking' => $tracking],
            ];
        }

        // Live shipment creation with GHN API v2
        try {
            $address = $order->shippingAddress;
            $items = $order->items()->where('type', 'product')->get();

            $ghnItems = $items->map(fn($item) => [
                'name' => (string) ($item->name ?? 'Sản phẩm'),
                'quantity' => (int) $item->qty,
                'price' => (int) $item->unit_price,
            ])->toArray();

            $payload = [
                'payment_type_id' => 1, // Người bán/Shop trả cước
                'required_note' => 'KHONGCHOXEMHANG',
                'to_name' => (string) ($address?->first_name . ' ' . $address?->last_name),
                'to_phone' => (string) ($address?->phone ?? '0900000000'),
                'to_address' => (string) ($address?->address_detail ?? '123 Đường ABC'),
                'to_ward_code' => (string) ($address?->ward_id ?? '20101'),
                'to_district_id' => (int) ($address?->city_id ?? 1444),
                'weight' => 500,
                'length' => 20,
                'width' => 15,
                'height' => 10,
                'service_id' => 53320,
                'service_type_id' => 2,
                'items' => $ghnItems,
            ];

            $url = $this->getBaseUrl() . 'v2/shipping-order/create';
            $response = Http::withHeaders($this->getHeaders())->timeout(8)->post($url, $payload);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                $orderCode = $response->json()['data']['order_code'] ?? null;
                return [
                    'success' => true,
                    'tracking_number' => $orderCode,
                    'reference' => $orderCode,
                    'carrier' => 'ghn',
                    'raw' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to create GHN order',
                'raw' => $response->json() ?? ['body' => $response->body()],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
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
            $url = $this->getBaseUrl() . 'v2/switch-status/cancel';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, [
                'order_codes' => [$trackingNumber],
            ]);

            return $response->successful() && ($response->json()['code'] ?? 0) === 200;
        } catch (\Exception $e) {
            $this->log('error', 'Failed to cancel GHN shipment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getTracking(string $trackingNumber): array
    {
        if (!$this->isConfigured() || str_starts_with($trackingNumber, 'GHN-TEST-')) {
            return [
                'tracking_number' => $trackingNumber,
                'carrier' => 'ghn',
                'status' => 'processing',
                'status_text' => 'Đang xử lý tại trung tâm điều phối',
                'history' => [
                    ['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Đã tiếp nhận đơn hàng tại bưu cục'],
                ],
            ];
        }

        try {
            $url = $this->getBaseUrl() . 'v2/shipping-order/detail';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, [
                'order_code' => $trackingNumber,
            ]);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                $data = $response->json()['data'] ?? [];
                $status = $data['status'] ?? 'unknown';

                return [
                    'tracking_number' => $trackingNumber,
                    'carrier' => 'ghn',
                    'status' => $status,
                    'status_text' => $data['status_name'] ?? $status,
                    'history' => $data['log'] ?? [],
                ];
            }
        } catch (\Exception $e) {
            $this->log('error', 'Tracking query failed: ' . $e->getMessage());
        }

        return [
            'tracking_number' => $trackingNumber,
            'carrier' => 'ghn',
            'status' => 'unknown',
            'status_text' => 'Không tìm thấy thông tin vận đơn',
            'history' => [],
        ];
    }
}
