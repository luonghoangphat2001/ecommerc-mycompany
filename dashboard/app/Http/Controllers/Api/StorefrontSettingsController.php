<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use App\Ecommerce\Settings\Contracts\StorefrontSettingsServiceInterface;
use App\Swagger\Attributes\ApiGet;
use OpenApi\Attributes as OAT;

class StorefrontSettingsController extends BaseApiController
{

    protected StorefrontSettingsServiceInterface $settingsService;

    public function __construct(StorefrontSettingsServiceInterface $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    #[ApiGet(
        path: '/settings',
        summary: 'Get Storefront Settings',
        tags: 'Storefront - Settings',
        requiresAuth: false,
        responseData: [
            new OAT\Property(property: 'general', type: 'object'),
            new OAT\Property(property: 'social', type: 'object'),
            new OAT\Property(property: 'store', type: 'object')
        ]
    )]
    public function index(): JsonResponse
    {
        return $this->success($this->settingsService->getStorefrontSettings());
    }
}

