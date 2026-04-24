<?php

namespace App\Services\Location\Providers;

use App\Contracts\Location\LocationProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GlobalLocationProvider implements LocationProviderInterface
{
    public function getCountries(): array
    {
        return Cache::remember('global_countries_iso', 604800, function () {
            try {
                $response = Http::timeout(5)->get('https://restcountries.com/v3.1/all?fields=cca2,name');
                if ($response->successful()) {
                    $countries = collect($response->json())->mapWithKeys(function ($country) {
                        return [strtoupper($country['cca2']) => $country['name']['common']];
                    })->toArray();
                    asort($countries);
                    return $countries;
                }
            } catch (\Exception $e) {}
            
            return ['VN' => 'Vietnam', 'US' => 'United States'];
        });
    }

    public function getStates(?string $countryCode): array
    {
        if (!$countryCode) return [];

        // For Global, we might return US states as a fallback or if country is US
        if (strtoupper($countryCode) === 'US') {
            return [
                'CA' => 'California',
                'NY' => 'New York',
                'TX' => 'Texas',
                // ... more states can be added or fetched from an API
            ];
        }

        return [];
    }

    public function getCities(?string $countryCode, ?string $stateId): array
    {
        return [];
    }

    public function getWards(?string $countryCode, ?string $stateId, ?string $cityId): array
    {
        return [];
    }
}
