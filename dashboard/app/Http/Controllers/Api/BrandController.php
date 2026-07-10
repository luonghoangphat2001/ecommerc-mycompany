<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\API\Brand\StoreBrandRequest;
use App\Http\Requests\API\Brand\UpdateBrandRequest;
use App\Http\Resources\Api\BrandResource;
use App\Models\Brand;
use App\Ecommerce\Product\Contracts\BrandServiceInterface;
use App\Ecommerce\Product\DTOs\Brand\BrandDTO;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class BrandController extends BaseApiController
{

    public function __construct(
        protected BrandServiceInterface $brandService
    ) {}

    #[ApiList(
        path: '/brands',
        summary: 'List of Brands',
        tags: 'Storefront - Brands',
        requiresAuth: false,
        responseData: '#/components/schemas/BrandResource'
    )]
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $brands = $this->brandService->paginateBrands($perPage);
        return $this->ok(BrandResource::collection($brands));
    }

    #[ApiPost(
        path: '/brands',
        summary: 'Create Brand',
        tags: 'Storefront - Brands',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['name', 'slug'],
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'Nike'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'nike'),
                    new OAT\Property(property: 'description', type: 'string', nullable: true, example: 'Nike brand'),
                    new OAT\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        responseData: '#/components/schemas/BrandResource'
    )]
    public function store(StoreBrandRequest $request)
    {
        $dto = BrandDTO::fromRequest($request);
        $brand = $this->brandService->createBrand($dto->toArray());
        
        return $this->created(new BrandResource($brand));
    }

    #[ApiGet(
        path: '/brands/{brand}',
        summary: 'Brand Details',
        tags: 'Storefront - Brands',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'brand', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Brand ID')
        ],
        responseData: '#/components/schemas/BrandResource'
    )]
    public function show(Brand $brand)
    {
        return $this->ok(new BrandResource($brand));
    }

    #[ApiUpdate(
        path: '/brands/{brand}',
        summary: 'Update Brand',
        tags: 'Storefront - Brands',
        parameters: [
            new OAT\Parameter(name: 'brand', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Brand ID')
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'Nike Updated'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'nike-updated'),
                    new OAT\Property(property: 'description', type: 'string', nullable: true, example: 'Nike brand'),
                    new OAT\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        responseData: '#/components/schemas/BrandResource'
    )]
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $dto = BrandDTO::fromRequest($request);
        $this->brandService->updateBrand($brand->id, $dto->toArray());
        
        return $this->ok(new BrandResource($brand->fresh()));
    }

    #[ApiDelete(
        path: '/brands/{brand}',
        summary: 'Delete Brand',
        tags: 'Storefront - Brands',
        parameters: [
            new OAT\Parameter(name: 'brand', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Brand ID')
        ]
    )]
    public function destroy(Brand $brand)
    {
        $this->brandService->deleteBrand($brand->id);
        return $this->noContent();
    }
}
