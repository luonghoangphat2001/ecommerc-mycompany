<?php

namespace App\Services\Address\Drivers;

use App\Services\Address\Contracts\AddressProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VietnamAddressDriver implements AddressProviderInterface
{
    private const BASE_URL = 'https://esgoo.net/api-tinhthanh';

    public function getStates(): array
    {
        return Cache::remember('vn_states_dynamic', 604800, function () {
            try {
                $response = Http::timeout(5)->get(self::BASE_URL . '/1/0.htm');
                if ($response->successful()) {
                    return collect($response->json()['data'] ?? [])->pluck('name', 'name')->toArray();
                }
            } catch (\Exception $e) {}
            return [];
        });
    }

    public function getRegions(string $stateId): array
    {
        return Cache::remember('vn_regions_' . md5($stateId), 604800, function () use ($stateId) {
            try {
                // We need to resolve the ID first as esgoo uses numeric IDs for children
                $provinces = Http::timeout(5)->get(self::BASE_URL . '/1/0.htm')->json();
                $province = collect($provinces['data'] ?? [])->firstWhere('name', $stateId);
                
                if (!$province) return [];

                $response = Http::timeout(5)->get(self::BASE_URL . '/2/' . $province['id'] . '.htm');
                if ($response->successful()) {
                    return collect($response->json()['data'] ?? [])->pluck('name', 'name')->toArray();
                }
            } catch (\Exception $e) {}
            return [];
        });
    }

    public function getSubRegions(string $stateId, string $regionId): array
    {
        return Cache::remember('vn_subregions_' . md5($stateId . $regionId), 604800, function () use ($stateId, $regionId) {
            try {
                $provinces = Http::timeout(5)->get(self::BASE_URL . '/1/0.htm')->json();
                $province = collect($provinces['data'] ?? [])->firstWhere('name', $stateId);
                if (!$province) return [];

                $districts = Http::timeout(5)->get(self::BASE_URL . '/2/' . $province['id'] . '.htm')->json();
                $district = collect($districts['data'] ?? [])->firstWhere('name', $regionId);
                if (!$district) return [];

                $response = Http::timeout(5)->get(self::BASE_URL . '/3/' . $district['id'] . '.htm');
                if ($response->successful()) {
                    return collect($response->json()['data'] ?? [])->pluck('name', 'name')->toArray();
                }
            } catch (\Exception $e) {}
            return [];
        });
    }
}
