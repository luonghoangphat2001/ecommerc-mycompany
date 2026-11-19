<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class GhtkCarrierAdapter extends BaseCarrierAdapter
{
    protected string $carrierId = 'ghtk';

    protected string $carrierName = 'Giao Hàng Tiết Kiệm (GHTK)';

    protected bool $supportsDomestic = true;

    private const SANDBOX_URL = 'https://services.ghtklab.com/';

    private const PRODUCTION_URL = 'https://services.giaohangtietkiem.vn/';

    public function isConfigured(): bool
    {
        return $this->hasConfigValues('api_token');
    }

    protected function getBaseUrl(): string
    {
        return $this->environmentUrl(self::SANDBOX_URL, self::PRODUCTION_URL);
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
                    return $this->successResponse(
                        'Kết nối API GHTK thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                        $json
                    );
                }
            }

            return $this->failureWithRaw(
                'Lỗi kết nối GHTK: ' . ($response->json()['message'] ?? $response->body()),
                $response->json() ?? ['raw' => $response->body()]
            );
        } catch (\Exception $e) {
            $this->log('error', 'GHTK connection test failed: ' . $e->getMessage());

            return $this->exceptionResponse('Không thể kết nối đến máy chủ GHTK: ' . $e->getMessage(), $e);
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
            ['service_id' => 'road', 'service_name' => 'GHTK Đường bộ', 'transport' => 'road'],
            ['service_id' => 'fly', 'service_name' => 'GHTK Đường hàng không (Nhanh)', 'transport' => 'fly'],
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
            $url = $this->getBaseUrl() . 'services/shipment/fee';

            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url, [
                'pick_province' => (string) $this->configValue('sender_province', 'Hồ Chí Minh'),
                'pick_district' => (string) $this->configValue('sender_district', 'Quận 1'),
                'province' => (string) ($destination['state'] ?? 'Hồ Chí Minh'),
                'district' => (string) ($destination['city'] ?? 'Quận 1'),
                'address' => (string) ($destination['address_detail'] ?? ''),
                'weight' => $weight,
                'value' => (int) ($destination['subtotal'] ?? 100000),
                'transport' => $serviceCode === 'fly' ? 'fly' : 'road',
            ]);

            if ($response->successful() && ($response->json()['success'] ?? false) === true) {
                $fee = (int) ($response->json()['fee']['fee'] ?? 0);

                return $this->rateResponse(
                    $this->applyMarkup($fee),
                    $serviceCode === 'fly' ? 'GHTK Hàng không' : 'GHTK Tiêu chuẩn',
                    $serviceCode === 'fly' ? 2 : 3
                );
            }
        } catch (\Exception $e) {
            $this->log('error', 'GHTK calculateRate error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function createShipment(Order $order, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return $this->credentialsMissing('GHTK API');
        }

        if ($this->isSandbox() && empty($options['force_real_api'])) {
            $tracking = 'GHTK-TEST-' . strtoupper(substr(md5($order->id . time()), 0, 8));

            return $this->sandboxShipmentResponse($order, $tracking);
        }

        return $this->liveShipmentApprovalRequired();
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
            $url = $this->getBaseUrl() . 'services/shipment/cancel/' . $trackingNumber;
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->post($url);

            return $response->successful() && ($response->json()['success'] ?? false) === true;
        } catch (\Exception $e) {
            $this->log('error', 'Failed to cancel GHTK shipment: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTracking(string $trackingNumber): array
    {
        if (! $this->isConfigured() || str_starts_with($trackingNumber, 'GHTK-TEST-')) {
            return $this->trackingResponse(
                $trackingNumber,
                'processing',
                'GHTK đang xử lý đơn hàng',
                [['time' => now()->format('Y-m-d H:i:s'), 'status' => 'Tiếp nhận đơn hàng thành công']]
            );
        }

        try {
            $url = $this->getBaseUrl() . 'services/shipment/v2/' . $trackingNumber;
            $response = Http::withHeaders($this->getHeaders())->timeout(5)->get($url);

            if ($response->successful() && ($response->json()['success'] ?? false) === true) {
                $orderData = $response->json()['order'] ?? [];

                return $this->trackingResponse(
                    $trackingNumber,
                    (string) ($orderData['status'] ?? 'unknown'),
                    (string) ($orderData['status_text'] ?? 'Đang giao hàng'),
                    $orderData['history'] ?? []
                );
            }
        } catch (\Exception $e) {
            $this->log('error', 'GHTK tracking error: ' . $e->getMessage());
        }

        return $this->unknownTracking($trackingNumber);
    }
}
