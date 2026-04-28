<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Ecommerce\Settings\Contracts\StorefrontSettingsServiceInterface;

use App\Traits\ApiResponse;

class StorefrontSettingsController extends Controller
{
    use ApiResponse;

    protected StorefrontSettingsServiceInterface $settingsService;

    public function __construct(StorefrontSettingsServiceInterface $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function index(): JsonResponse
    {
        return $this->success($this->settingsService->getStorefrontSettings());
    }
}

