<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class ViettelPostCarrierAdapter extends BaseCarrierAdapter
{
    protected string $carrierId = 'viettel_post';

    protected string $carrierName = 'Viettel Post';

    protected bool $supportsDomestic = true;

    private const BASE_URL = 'https://partner.viettelpost.vn/v2/';

    public function isConfigured(): bool
    {
        return $this->hasConfigValues('api_token');
    }

    protected function getHeaders(): array
    {
        return $this->jsonTokenHeaders();
    }

    /**
     * {@inheritDoc}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return $this->missingCredentialsResponse('Chưa cấu hình API credentials (API Token còn trống).');
        }

        try {
            $url = self::BASE_URL . 'categories/listService';
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url, ['TYPE' => 1]);

            if ($response->successful()) {
                $json = $response->json();
                if (($json['status'] ?? 0) === 200) {
                    return $this->successResponse('Kết nối API Viettel Post thành công.', [
                        'status' => 200,
                        'services_count' => count($json['data'] ?? []),
                    ]);
                }
            }

            return $this->failureWithRaw(
                'Lỗi kết nối Viettel Post: ' . ($response->json()['message'] ?? $response->body()),
                $response->json() ?? ['raw' => $response->body()]
            );
        } catch (\Exception $e) {
            $this->log('error', 'Viettel Post connection test failed: ' . $e->getMessage());

            return $this->exceptionResponse('Không thể kết nối đến máy chủ Viettel Post: ' . $e->getMessage(), $e);
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

        return [
            ['service_id' => 'VCN', 'service_name' => 'Chuyển phát nhanh (VCN)', 'estimated_days' => 2],
            ['service_id' => 'VTK', 'service_name' => 'Chuyển phát tiết kiệm (VTK)', 'estimated_days' => 4],
            ['service_id' => 'VHT', 'service_name' => 'Chuyển phát hỏa tốc (VHT)', 'estimated_days' => 1],
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
            $weight = $this->packageValue($package, 'weight', 500, 100);
            $url = self::BASE_URL . 'order/getPrice';

            $payload = [
                'PRODUCT_WEIGHT' => $weight,
                'PRODUCT_PRICE' => (int) ($destination['subtotal'] ?? 100000),
                'MONEY_COLLECTION' => 0,
                'ORDER_SERVICE' => $serviceCode ?: 'VCN',
                'SENDER_PROVINCE' => (int) $this->configValue('sender_province_id', 1),
                'SENDER_DISTRICT' => (int) $this->configValue('sender_district_id', 1),
                'RECEIVER_PROVINCE' => (int) ($destination['province_id'] ?? 1),
                'RECEIVER_DISTRICT' => (int) ($destination['district_id'] ?? 1),
                'PRODUCT_TYPE' => 'HH',
                'TYPE' => 1,
            ];

            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url, $payload);
            if ($response->successful() && ($response->json()['status'] ?? 0) === 200) {
                $fee = (int) ($response->json()['data']['MONEY_TOTAL'] ?? 0);

                return $this->rateResponse($this->applyMarkup($fee), 'Viettel Post ' . $serviceCode, 2);
            }
        } catch (\Exception $e) {
            $this->log('error', 'Viettel Post calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return $this->credentialsMissing('Viettel Post API');
        }

        if ($this->isSandbox() && empty($options['force_real_api'])) {
            $tracking = 'VT-' . strtoupper(substr(md5($order->id . time()), 0, 9));

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
            'delivering',
            'Bưu cục Viettel Post đang chuyển tiếp hàng',
            [['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Nhận bưu phẩm tại điểm giao dịch']]
        );
    }
}
