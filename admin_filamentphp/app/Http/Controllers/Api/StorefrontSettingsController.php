<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Contracts\Services\StorefrontSettingsServiceInterface;

class StorefrontSettingsController extends Controller
{
    protected StorefrontSettingsServiceInterface $settingsService;

    public function __construct(StorefrontSettingsServiceInterface $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->settingsService->getStorefrontSettings()
        ]);
    }
}

