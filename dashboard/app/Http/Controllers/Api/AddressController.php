<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Ecommerce\Address\Services\Contracts\AddressServiceInterface;
use App\Ecommerce\Address\Contracts\AddressBookServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class AddressController extends BaseApiController
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

    #[ApiGet(
        path: '/addresses/countries',
        summary: 'List of Countries',
        tags: 'Storefront - Address',
        requiresAuth: false,
        responseData: [
            new OAT\Property(property: 'code', type: 'string', example: 'VN'),
            new OAT\Property(property: 'name', type: 'string', example: 'Vietnam')
        ]
    )]
    public function countries(): JsonResponse
    {
        $countries = $this->addressService->getCountries();
        
        return $this->ok($countries);
    }

    #[ApiGet(
        path: '/addresses/countries/{countryCode}/states',
        summary: 'List of States by Country',
        tags: 'Storefront - Address',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'countryCode', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'VN'), description: 'Country code')
        ],
        responseData: [
            new OAT\Property(property: 'id', type: 'string', example: 'SG'),
            new OAT\Property(property: 'name', type: 'string', example: 'Hồ Chí Minh')
        ]
    )]
    public function states(string $countryCode): JsonResponse
    {
        $states = $this->addressService->getStates($countryCode);
        
        return $this->ok($states);
    }

    #[ApiGet(
        path: '/addresses/countries/{countryCode}/states/{stateId}/regions',
        summary: 'List of Regions by State',
        tags: 'Storefront - Address',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'countryCode', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'VN'), description: 'Country code'),
            new OAT\Parameter(name: 'stateId', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'SG'), description: 'State ID')
        ],
        responseData: [
            new OAT\Property(property: 'id', type: 'string', example: 'Q1'),
            new OAT\Property(property: 'name', type: 'string', example: 'Quận 1')
        ]
    )]
    public function regions(string $countryCode, string $stateId): JsonResponse
    {
        $regions = $this->addressService->getRegions($countryCode, $stateId);
        
        return $this->ok($regions);
    }

    #[ApiGet(
        path: '/addresses/countries/{countryCode}/states/{stateId}/regions/{regionId}/sub-regions',
        summary: 'List of Sub-Regions by Region',
        tags: 'Storefront - Address',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'countryCode', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'VN'), description: 'Country code'),
            new OAT\Parameter(name: 'stateId', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'SG'), description: 'State ID'),
            new OAT\Parameter(name: 'regionId', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'Q1'), description: 'Region ID')
        ],
        responseData: [
            new OAT\Property(property: 'id', type: 'string', example: 'PBN'),
            new OAT\Property(property: 'name', type: 'string', example: 'Phường Bến Nghé')
        ]
    )]
    public function subRegions(string $countryCode, string $stateId, string $regionId): JsonResponse
    {
        $subRegions = $this->addressService->getSubRegions($countryCode, $stateId, $regionId);
        
        return $this->ok($subRegions);
    }

    #[ApiGet(
        path: '/user-addresses',
        summary: 'List of User Addresses',
        tags: 'Storefront - User Address',
        responseData: [
            new OAT\Property(property: 'id', type: 'integer', example: 1),
            new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
            new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
            new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
            new OAT\Property(property: 'street', type: 'string', example: '123 Le Loi'),
            new OAT\Property(property: 'city', type: 'string', example: 'Ho Chi Minh'),
            new OAT\Property(property: 'country', type: 'string', example: 'VN')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $addresses = $this->addressBookService->listAddresses($user->id);
        
        return $this->ok(\App\Http\Resources\Api\AddressResource::collection($addresses));
    }

    #[ApiPost(
        path: '/user-addresses',
        summary: 'Add New Address',
        tags: 'Storefront - User Address',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['first_name', 'last_name', 'phone', 'country_code', 'street'],
                properties: [
                    new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
                    new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
                    new OAT\Property(property: 'company', type: 'string', nullable: true, example: 'HP Platform'),
                    new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
                    new OAT\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'a@example.com'),
                    new OAT\Property(property: 'type', type: 'string', enum: ['shipping', 'billing'], example: 'shipping'),
                    new OAT\Property(property: 'country_code', type: 'string', example: 'VN'),
                    new OAT\Property(property: 'state_id', type: 'string', nullable: true, example: 'SG'),
                    new OAT\Property(property: 'city_id', type: 'string', nullable: true, example: 'HCM'),
                    new OAT\Property(property: 'ward_id', type: 'string', nullable: true, example: 'W1'),
                    new OAT\Property(property: 'street', type: 'string', example: '123 Le Loi'),
                    new OAT\Property(property: 'zip', type: 'string', nullable: true, example: '700000')
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'id', type: 'integer', example: 1),
            new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
            new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
            new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
            new OAT\Property(property: 'street', type: 'string', example: '123 Le Loi'),
            new OAT\Property(property: 'city', type: 'string', example: 'Ho Chi Minh'),
            new OAT\Property(property: 'country', type: 'string', example: 'VN')
        ]
    )]
    public function store(\App\Http\Requests\API\StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $address = $this->addressBookService->addAddress($user->id, $validated);

        return $this->created(
            new \App\Http\Resources\Api\AddressResource($address),
            __('messages.address_created') ?? 'Address saved successfully'
        );
    }

    #[ApiUpdate(
        path: '/user-addresses/{address}',
        summary: 'Update Address',
        tags: 'Storefront - User Address',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
                    new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
                    new OAT\Property(property: 'company', type: 'string', nullable: true, example: 'HP Platform'),
                    new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
                    new OAT\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'a@example.com'),
                    new OAT\Property(property: 'type', type: 'string', enum: ['shipping', 'billing'], example: 'shipping'),
                    new OAT\Property(property: 'country_code', type: 'string', example: 'VN'),
                    new OAT\Property(property: 'state_id', type: 'string', nullable: true, example: 'SG'),
                    new OAT\Property(property: 'city_id', type: 'string', nullable: true, example: 'HCM'),
                    new OAT\Property(property: 'ward_id', type: 'string', nullable: true, example: 'W1'),
                    new OAT\Property(property: 'street', type: 'string', example: '123 Le Loi'),
                    new OAT\Property(property: 'zip', type: 'string', nullable: true, example: '700000')
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'id', type: 'integer', example: 1),
            new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
            new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
            new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
            new OAT\Property(property: 'street', type: 'string', example: '123 Le Loi'),
            new OAT\Property(property: 'city', type: 'string', example: 'Ho Chi Minh'),
            new OAT\Property(property: 'country', type: 'string', example: 'VN')
        ]
    )]
    public function update(\App\Http\Requests\API\UpdateAddressRequest $request, $addressId): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            $address = $this->addressBookService->updateAddress($user->id, (int) $addressId, $validated);
            return $this->ok(
                new \App\Http\Resources\Api\AddressResource($address),
                __('messages.address_updated') ?? 'Address updated successfully'
            );
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    #[ApiDelete(
        path: '/user-addresses/{address}',
        summary: 'Delete Address',
        tags: 'Storefront - User Address',
        parameters: [
            new OAT\Parameter(name: 'address', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Address ID')
        ],
        responseData: [
            new OAT\Property(property: 'deleted', type: 'boolean', example: true)
        ]
    )]
    public function destroy(Request $request, $addressId): JsonResponse
    {
        $user = $request->user();
        $deleted = $this->addressBookService->deleteAddress($user->id, (int) $addressId);

        if (!$deleted) {
            return $this->badRequest(__('messages.address_delete_failed') ?? 'Address deletion failed');
        }

        return $this->ok(
            ['deleted' => true],
            __('messages.address_deleted') ?? 'Address removed successfully'
        );
    }
}
