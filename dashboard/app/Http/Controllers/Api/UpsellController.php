<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UpsellResource;
use App\Ecommerce\Upsell\Contracts\UpsellServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiList;

class UpsellController extends Controller
{
    use ApiResponse;

    protected UpsellServiceInterface $upsellService;

    public function __construct(UpsellServiceInterface $upsellService)
    {
        $this->upsellService = $upsellService;
    }

    #[ApiList(
        path: '/api/storefront/v1/products/{productId}/upsells',
        summary: 'List Upsells for Product',
        tags: 'Storefront - Upsells',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'productId', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Original Product ID')
        ],
        responseData: '#/components/schemas/UpsellResource'
    )]
    public function index(int $productId): JsonResponse
    {
        $upsells = $this->upsellService->getUpsellsForProduct($productId);
        
        // Load relationships on each upsell item
        $upsells->each(function ($item) {
            if ($item->relationLoaded('upsellProduct')) {
                $item->upsellProduct->load('featuredImage', 'categories', 'brand');
            }
        });
        
        return $this->ok(UpsellResource::collection($upsells));
    }
}
