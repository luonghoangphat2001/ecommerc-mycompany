<?php

namespace App\Ecommerce\Shipping\Carriers\Adapters;

use App\Ecommerce\Shipping\Carriers\BaseCarrierAdapter;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class UpsCarrierAdapter extends BaseCarrierAdapter
{
    protected string $carrierId = 'ups';

    protected string $carrierName = 'UPS';

    protected bool $supportsInternational = true;

    private const SANDBOX_URL = 'https://wwwcie.ups.com/api/';

    private const PRODUCTION_URL = 'https://onlinetools.ups.com/api/';

    public function isConfigured(): bool
    {
        return $this->hasConfigValues('client_id', 'client_secret');
    }

    protected function getBaseUrl(): string
    {
        return $this->environmentUrl(self::SANDBOX_URL, self::PRODUCTION_URL);
    }

    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return $this->missingCredentialsResponse('Chưa cấu hình API credentials UPS (Client ID / Secret trống).');
        }

        try {
            $config = $this->getConfig();
            $url = $this->getBaseUrl() . 'security/v1/oauth/token';

            $response = Http::asForm()
                ->withBasicAuth((string) $config['client_id'], (string) $config['client_secret'])
                ->timeout(5)
                ->post($url, ['grant_type' => 'client_credentials']);

            if ($response->successful() && ! empty($response->json()['access_token'])) {
                return $this->successResponse(
                    'Kết nối API UPS OAuth thành công (' . ($this->isSandbox() ? 'Sandbox' : 'Production') . ').',
                    ['status' => 200]
                );
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối UPS: ' . ($response->json()['response']['errors'][0]['message'] ?? $response->body()),
                'details' => $response->json() ?? ['status' => $response->status()],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'UPS Connection error: ' . $e->getMessage()];
        }
    }

    public function supportsDestination(string $country, ?string $state = null): bool
    {
        return strtoupper(trim($country)) !== 'VN';
    }

    public function getAvailableServices(array $destination, array $package = []): array
    {
        return [
            ['service_id' => '07', 'service_name' => 'UPS Worldwide Express', 'estimated_days' => 3],
            ['service_id' => '08', 'service_name' => 'UPS Worldwide Expedited', 'estimated_days' => 5],
        ];
    }

    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array
    {
        return null;
    }

    public function createShipment(Order $order, array $options = []): array
    {
        return $this->sandboxShipmentResponse($order, '1Z9999999999999999', null, false);
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        return false;
    }

    public function getTracking(string $trackingNumber): array
    {
        return $this->trackingResponse($trackingNumber, 'transit', 'On the way to destination');
    }
}
