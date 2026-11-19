<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;

class VnPostCarrierAdapter extends BaseCarrierAdapter
{
    protected string $carrierId = 'vnpost';

    protected string $carrierName = 'Vietnam Post (VNPost)';

    protected bool $supportsDomestic = true;

    protected bool $supportsInternational = true;

    private const BASE_URL = 'https://api.vnpost.vn/';

    public function isConfigured(): bool
    {
        return $this->hasConfigValues('api_token');
    }

    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return $this->missingCredentialsResponse('Chưa cấu hình API Token VNPost.');
        }

        return $this->successResponse('Đã xác nhận thông số kết nối VNPost.', ['status' => 'configured']);
    }

    public function supportsDestination(string $country, ?string $state = null): bool
    {
        return true;
    }

    public function getAvailableServices(array $destination, array $package = []): array
    {
        return [
            ['service_id' => 'EMS', 'service_name' => 'VNPost Chuyển phát nhanh EMS', 'estimated_days' => 2],
            ['service_id' => 'BK', 'service_name' => 'VNPost Bưu phẩm thường', 'estimated_days' => 5],
        ];
    }

    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        return null;
    }

    public function createShipment(Order $order, array $options = []): array
    {
        return $this->sandboxShipmentResponse(
            $order,
            'VNP-' . strtoupper(substr(md5($order->id . time()), 0, 9)),
            null,
            false
        );
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        return false;
    }

    public function getTracking(string $trackingNumber): array
    {
        return $this->trackingResponse($trackingNumber, 'delivering', 'Bưu cục VNPost đang phân loại bưu gửi');
    }
}
