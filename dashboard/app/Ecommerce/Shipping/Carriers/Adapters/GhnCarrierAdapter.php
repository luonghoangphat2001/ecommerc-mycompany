<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class GhnCarrierAdapter extends BaseCarrierAdapter
{
    protected string $carrierId = 'ghn';

    protected string $carrierName = 'Giao Hàng Nhanh (GHN)';

    protected bool $supportsDomestic = true;

    private const SANDBOX_URL = 'https://dev-online-gateway.ghn.vn/shiip/public-api/';

    private const PRODUCTION_URL = 'https://online-gateway.ghn.vn/shiip/public-api/';

    public function isConfigured(): bool
    {
        return $this->hasConfigValues('api_token', 'shop_id');
    }

    protected function getBaseUrl(): string
    {
        return $this->environmentUrl(self::SANDBOX_URL, self::PRODUCTION_URL);
    }

    protected function getHeaders(): array
    {
        return $this->jsonTokenHeaders(['ShopId' => (string) $this->configValue('shop_id', '')]);
    }

    /**
     * {@inheritDoc}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return $this->missingCredentialsResponse('Chưa cấu hình API credentials (API Token hoặc Shop ID còn trống).');
        }

        try {
            $url = $this->getBaseUrl() . 'master-data/province';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                return $this->successResponse(
                    'Kết nối API GHN thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                    [
                        'code' => $response->json()['code'],
                        'mode' => $this->isSandbox() ? 'sandbox' : 'production',
                        'data_sample' => count($response->json()['data'] ?? []) . ' provinces available',
                    ]
                );
            }

            return $this->failureWithRaw(
                'Lỗi kết nối GHN: ' . ($response->json()['message'] ?? $response->body()),
                $response->json() ?? ['raw' => $response->body()]
            );
        } catch (\Exception $e) {
            $this->log('error', 'Connection test failed: ' . $e->getMessage());

            return $this->exceptionResponse('Không thể kết nối đến máy chủ GHN: ' . $e->getMessage(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDestination(string $country, ?string $state = null): bool
    {
        return strtoupper(trim($country)) === 'VN';
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableServices(array $destination, array $package = []): array
    {
        if (! $this->supportsDestination($destination['country'] ?? 'VN')) {
            return [];
        }

        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $fromDistrict = (int) $this->configValue('sender_district_id', 1442); // Default Quận 1 HCM
            $toDistrict = (int) ($destination['district_id'] ?? 1444);

            $url = $this->getBaseUrl() . 'v2/shipping-order/available-services';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, [
                'shop_id' => (int) $this->configValue('shop_id', 0),
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
     * {@inheritDoc}
     */
    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        if (! $this->canCalculateRate($destination)) {
            return null;
        }

        try {
            $dimensions = $this->packageDimensions($package);

            $payload = [
                'service_id' => is_numeric($serviceCode) ? (int) $serviceCode : 53320,
                'service_type_id' => 2,
                'to_district_id' => (int) ($destination['district_id'] ?? 1444),
                'to_ward_code' => (string) ($destination['ward_code'] ?? '20101'),
                ...$dimensions,
                'insurance_value' => min(5000000, (int) ($destination['subtotal'] ?? 0)),
            ];

            $url = $this->getBaseUrl() . 'v2/shipping-order/fee';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, $payload);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                $totalFee = (int) ($response->json()['data']['total'] ?? 0);
                $finalAmount = $this->applyMarkup($totalFee);

                return $this->rateResponse($finalAmount, 'GHN Tiêu chuẩn', 2);
            }

            $this->log('warning', 'GHN fee API returned error: ' . $response->body());
        } catch (\Exception $e) {
            $this->log('error', 'GHN calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return $this->credentialsMissing('GHN API');
        }

        if ($this->isSandbox() && empty($options['force_real_api'])) {
            $tracking = 'GHN-TEST-' . strtoupper(substr(md5($order->id . time()), 0, 8));

            return $this->sandboxShipmentResponse($order, $tracking);
        }

        // Live shipment creation with GHN API v2
        try {
            $address = $order->shippingAddress;
            $items = $order->items()->where('type', 'product')->get();

            $ghnItems = $items->map(fn ($item) => [
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
                ...$this->packageDimensions([]),
                'service_id' => 53320,
                'service_type_id' => 2,
                'items' => $ghnItems,
            ];

            $url = $this->getBaseUrl() . 'v2/shipping-order/create';
            $response = Http::withHeaders($this->getHeaders())->timeout(8)->post($url, $payload);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                $orderCode = $response->json()['data']['order_code'] ?? null;

                return $this->shipmentResponse($orderCode, $orderCode, $response->json());
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to create GHN order',
                'raw' => $response->json() ?? ['body' => $response->body()],
            ];
        } catch (\Exception $e) {
            return $this->failure($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function cancelShipment(string $trackingNumber): bool
    {
        if (! $this->isConfigured()) {
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
     * {@inheritDoc}
     */
    public function getTracking(string $trackingNumber): array
    {
        if (! $this->isConfigured() || str_starts_with($trackingNumber, 'GHN-TEST-')) {
            return $this->trackingResponse(
                $trackingNumber,
                'processing',
                'Đang xử lý tại trung tâm điều phối',
                [['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Đã tiếp nhận đơn hàng tại bưu cục']]
            );
        }

        try {
            $url = $this->getBaseUrl() . 'v2/shipping-order/detail';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, [
                'order_code' => $trackingNumber,
            ]);

            if ($response->successful() && ($response->json()['code'] ?? 0) === 200) {
                $data = $response->json()['data'] ?? [];
                $status = $data['status'] ?? 'unknown';

                return $this->trackingResponse($trackingNumber, $status, $data['status_name'] ?? $status, $data['log'] ?? []);
            }
        } catch (\Exception $e) {
            $this->log('error', 'Tracking query failed: ' . $e->getMessage());
        }

        return $this->unknownTracking($trackingNumber);
    }
}
