<?php

namespace App\Ecommerce\Address\Services;

use App\Ecommerce\Address\Services\Contracts\AddressProviderInterface;
use App\Ecommerce\Address\Services\Contracts\AddressServiceInterface;
use App\Ecommerce\Address\Services\Drivers\VietnamAddressDriver;
use App\Ecommerce\Address\Services\Drivers\FallbackAddressDriver;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AddressService implements AddressServiceInterface
{
    /**
     * Resolve the appropriate driver for a given country.
     * 
     * @param mixed $countryCode
     * @return AddressProviderInterface
     */
    public function driver($countryCode): AddressProviderInterface
    {
        $code = is_array($countryCode) ? ($countryCode[0] ?? null) : $countryCode;
        
        if (!$code) {
            return new FallbackAddressDriver();
        }

        $code = strtolower((string) $code);

        return match ($code) {
            'vn' => new VietnamAddressDriver(),
            default => new FallbackAddressDriver(),
        };
    }

    /**
     * Global country list with cross-country API/DB fallback.
     */
    public function getCountries(): array
    {
        return Cache::remember('global_countries_unified', 604800, function () {
            try {
                $response = Http::timeout(5)->get('https://restcountries.com/v3.1/all?fields=cca2,name');
                if ($response->successful()) {
                    $countries = collect($response->json())->mapWithKeys(function ($country) {
                        return [strtolower($country['cca2']) => $country['name']['common']];
                    })->toArray();
                    asort($countries);
                    return $countries;
                }
            } catch (\Exception $e) {}
            
            if (class_exists(\Squire\Models\Country::class)) {
                return \Squire\Models\Country::pluck('name', 'id')->mapWithKeys(fn($v, $k) => [strtolower($k) => $v])->toArray();
            }
            
            return ['vn' => trans('address.vietnam') ?? 'Vietnam'];
        });
    }

    // ============================================
    // UNIFIED ACCESSORS (Proxied to Drivers)
    // ============================================

    public function getStates($countryCode): array
    {
        return $this->driver($countryCode)->getStates();
    }

    public function getRegions($countryCode, ?string $stateId): array
    {
        if (!$stateId) return [];
        return $this->driver($countryCode)->getRegions($stateId);
    }

    public function getSubRegions($countryCode, ?string $stateId, ?string $regionId): array
    {
        if (!$stateId || !$regionId) return [];
        return $this->driver($countryCode)->getSubRegions($stateId, $regionId);
    }
}
