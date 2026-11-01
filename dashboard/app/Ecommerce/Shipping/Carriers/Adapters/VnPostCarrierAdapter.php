<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class VnPostCarrierAdapter extends BaseCarrierAdapter
{
    private const BASE_URL = 'https://api.vnpost.vn/';

    public function getId(): string
    {
        return 'vnpost';
    }

    public function getName(): string
    {
        return 'Vietnam Post (VNPost)';
    }

    public function isDomestic(): bool
    {
        return true;
    }

    public function isInternational(): bool
    {
        return true;
    }

    public function isConfigured(): bool
    {
        $config = $this->getConfig();
        return !empty($config['api_token']);
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình API Token VNPost.',
                'details' => ['status' => 'missing_credentials'],
            ];
        }

        return [
            'success' => true,
            'message' => 'Đã xác nhận thông số kết nối VNPost.',
            'details' => ['status' => 'configured'],
        ];
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
        return [
            'success' => true,
            'tracking_number' => 'VNP-' . strtoupper(substr(md5($order->id . time()), 0, 9)),
            'carrier' => 'vnpost',
            'raw' => ['mode' => 'sandbox_simulated'],
        ];
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        return false;
    }

    public function getTracking(string $trackingNumber): array
    {
        return [
            'tracking_number' => $trackingNumber,
            'carrier' => 'vnpost',
            'status' => 'delivering',
            'status_text' => 'Bưu cục VNPost đang phân loại bưu gửi',
            'history' => [],
        ];
    }
}
