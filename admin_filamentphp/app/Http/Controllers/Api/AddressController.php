<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Address\Contracts\AddressServiceInterface;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    protected AddressServiceInterface $addressService;

    public function __construct(AddressServiceInterface $addressService)
    {
        $this->addressService = $addressService;
    }

    /**
     * Get list of countries
     */
    public function countries(): JsonResponse
    {
        $countries = $this->addressService->getCountries();
        
        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    /**
     * Get states/provinces by country code
     */
    public function states(string $countryCode): JsonResponse
    {
        $states = $this->addressService->getStates($countryCode);
        
        return response()->json([
            'success' => true,
            'data' => $states
        ]);
    }

    /**
     * Get districts/regions by country and state
     */
    public function regions(string $countryCode, string $stateId): JsonResponse
    {
        $regions = $this->addressService->getRegions($countryCode, $stateId);
        
        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }

    /**
     * Get wards/sub-regions by country, state and region
     */
    public function subRegions(string $countryCode, string $stateId, string $regionId): JsonResponse
    {
        $subRegions = $this->addressService->getSubRegions($countryCode, $stateId, $regionId);
        
        return response()->json([
            'success' => true,
            'data' => $subRegions
        ]);
    }
}
