<?php

namespace App\Ecommerce\Location\Services\Location\Providers;

use App\Ecommerce\Location\Contracts\LocationProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VietnamLocationProvider implements LocationProviderInterface
{
    private const BASE_URL = 'https://esgoo.net/api-tinhthanh';

    public function getCountries(): array
    {
        return ['VN' => 'Việt Nam'];
    }

    public function getStates(?string $countryCode): array
    {
        if (!$countryCode || strtoupper($countryCode) !== 'VN') return [];

        return Cache::remember('vn_provinces', 604800, function () {
            try {
                $response = Http::timeout(5)->get(self::BASE_URL . '/1/0.htm');
                if ($response->successful()) {
                    return collect($response->json()['data'] ?? [])->pluck('name', 'id')->toArray();
                }
            } catch (\Exception $e) {}
            return [];
        });
    }

    public function getCities(?string $countryCode, ?string $stateId): array
    {
        if (!$countryCode || !$stateId || strtoupper($countryCode) !== 'VN') return [];

        return Cache::remember('vn_districts_' . $stateId, 604800, function () use ($stateId) {
            try {
                $response = Http::timeout(5)->get(self::BASE_URL . '/2/' . $stateId . '.htm');
                if ($response->successful()) {
                    return collect($response->json()['data'] ?? [])->pluck('name', 'id')->toArray();
                }
            } catch (\Exception $e) {}
            return [];
        });
    }

    public function getWards(?string $countryCode, ?string $stateId, ?string $cityId): array
    {
        if (!$countryCode || !$stateId || !$cityId || strtoupper($countryCode) !== 'VN') return [];

        return Cache::remember('vn_wards_' . $cityId, 604800, function () use ($cityId) {
            try {
                $response = Http::timeout(5)->get(self::BASE_URL . '/3/' . $cityId . '.htm');
                if ($response->successful()) {
                    return collect($response->json()['data'] ?? [])->pluck('name', 'id')->toArray();
                }
            } catch (\Exception $e) {}
            return [];
        });
    }
}
