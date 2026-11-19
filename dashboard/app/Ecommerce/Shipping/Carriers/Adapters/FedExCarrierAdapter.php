<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class FedExCarrierAdapter extends BaseCarrierAdapter
{
    protected string $carrierId = 'fedex';

    protected string $carrierName = 'FedEx';

    protected bool $supportsInternational = true;

    private const SANDBOX_URL = 'https://apis-sandbox.fedex.com/';

    private const PRODUCTION_URL = 'https://apis.fedex.com/';

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
            return $this->missingCredentialsResponse('Chưa cấu hình API credentials FedEx (API Key hoặc Secret còn trống).');
        }

        try {
            $config = $this->getConfig();
            $url = $this->getBaseUrl() . 'oauth/token';

            $response = Http::asForm()->timeout(5)->post($url, [
                'grant_type' => 'client_credentials',
                'client_id' => (string) $config['client_id'],
                'client_secret' => (string) $config['client_secret'],
            ]);

            if ($response->successful() && ! empty($response->json()['access_token'])) {
                return $this->successResponse(
                    'Kết nối API FedEx OAuth 2.0 thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                    ['token_type' => $response->json()['token_type'] ?? 'Bearer']
                );
            }

            return $this->failureWithRaw(
                'Lỗi xác thực FedEx: ' . ($response->json()['errors'][0]['message'] ?? $response->body()),
                $response->json() ?? ['raw' => $response->body()]
            );
        } catch (\Exception $e) {
            $this->log('error', 'FedEx connection test error: ' . $e->getMessage());

            return $this->exceptionResponse('Không thể kết nối đến máy chủ FedEx: ' . $e->getMessage(), $e);
        }
    }

    public function supportsDestination(string $country, ?string $state = null): bool
    {
        return strtoupper(trim($country)) !== 'VN';
    }

    public function getAvailableServices(array $destination, array $package = []): array
    {
        return [
            ['service_id' => 'INTERNATIONAL_PRIORITY', 'service_name' => 'FedEx International Priority', 'estimated_days' => 3],
            ['service_id' => 'INTERNATIONAL_ECONOMY', 'service_name' => 'FedEx International Economy', 'estimated_days' => 6],
        ];
    }

    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        return null; // Awaiting live quote integration with account
    }

    public function createShipment(Order $order, array $options = []): array
    {
        if ($this->isSandbox()) {
            $tracking = 'FDX-' . strtoupper(substr(md5($order->id . time()), 0, 10));

            return $this->sandboxShipmentResponse($order, $tracking, 'SANDBOX-', false);
        }

        return $this->failure('Requires live FedEx credentials');
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        return false;
    }

    public function getTracking(string $trackingNumber): array
    {
        return $this->trackingResponse($trackingNumber, 'transit', 'In transit to destination country');
    }
}
