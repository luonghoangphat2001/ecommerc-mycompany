<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CrossSellResource;
use App\Ecommerce\CrossSell\Contracts\CrossSellServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiList;

class CrossSellController extends Controller
{
    use ApiResponse;

    protected CrossSellServiceInterface $crossSellService;

    public function __construct(CrossSellServiceInterface $crossSellService)
    {
        $this->crossSellService = $crossSellService;
    }

    #[ApiList(
        path: '/api/storefront/v1/products/{productId}/cross-sells',
        summary: 'List Cross-sells for Product',
        tags: 'Storefront - Cross-sells',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'productId', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Original Product ID')
        ],
        responseData: '#/components/schemas/CrossSellResource'
    )]
    public function index(int $productId): JsonResponse
    {
        $crossSells = $this->crossSellService->getCrossSellsForProduct($productId);
        
        // Load relationships on each cross-sell item
        $crossSells->each(function ($item) {
            if ($item->relationLoaded('crossSellProduct')) {
                $item->crossSellProduct->load('featuredImage', 'categories', 'brand');
            }
        });
        
        return $this->ok(CrossSellResource::collection($crossSells));
    }
}
