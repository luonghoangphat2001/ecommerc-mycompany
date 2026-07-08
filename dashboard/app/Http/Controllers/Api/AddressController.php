<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Ecommerce\Address\Services\Contracts\AddressServiceInterface;
use App\Ecommerce\Address\Contracts\AddressBookServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    protected AddressServiceInterface $addressService;
    protected AddressBookServiceInterface $addressBookService;

    public function __construct(
        AddressServiceInterface $addressService,
        AddressBookServiceInterface $addressBookService
    ) {
        $this->addressService = $addressService;
        $this->addressBookService = $addressBookService;
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

    /**
     * List user saved addresses
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $addresses = $this->addressBookService->listAddresses($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    /**
     * Save new address
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'company' => 'nullable|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'type' => 'nullable|string|in:shipping,billing',
            'country_code' => 'required|string|max:2',
            'state_id' => 'nullable|string',
            'city_id' => 'nullable|string',
            'ward_id' => 'nullable|string',
            'street' => 'required|string',
            'zip' => 'nullable|string',
        ]);

        $address = $this->addressBookService->addAddress($user->id, $validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.address_created') ?? 'Address saved successfully',
            'data' => $address
        ]);
    }

    /**
     * Update saved address
     */
    public function update(Request $request, $addressId): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'company' => 'nullable|string',
            'phone' => 'sometimes|string',
            'email' => 'nullable|email',
            'type' => 'nullable|string|in:shipping,billing',
            'country_code' => 'sometimes|string|max:2',
            'state_id' => 'nullable|string',
            'city_id' => 'nullable|string',
            'ward_id' => 'nullable|string',
            'street' => 'sometimes|string',
            'zip' => 'nullable|string',
        ]);

        try {
            $address = $this->addressBookService->updateAddress($user->id, (int) $addressId, $validated);
            return response()->json([
                'success' => true,
                'message' => __('messages.address_updated') ?? 'Address updated successfully',
                'data' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove saved address
     */
    public function destroy(Request $request, $addressId): JsonResponse
    {
        $user = $request->user();
        $deleted = $this->addressBookService->deleteAddress($user->id, (int) $addressId);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => __('messages.address_delete_failed') ?? 'Address deletion failed'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.address_deleted') ?? 'Address removed successfully'
        ]);
    }
}
