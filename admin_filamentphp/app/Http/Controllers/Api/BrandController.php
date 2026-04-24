<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Brand\StoreBrandRequest;
use App\Http\Requests\API\Brand\UpdateBrandRequest;
use App\Http\Resources\Api\BrandResource;
use App\Models\Brand;
use App\Contracts\Services\BrandServiceInterface;
use App\DTOs\Brand\BrandDTO;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @group Shop
 *
 * APIs for managing product brands.
 */
class BrandController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BrandServiceInterface $brandService
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $brands = $this->brandService->paginateBrands($perPage);
        return $this->ok(BrandResource::collection($brands));
    }

    public function store(StoreBrandRequest $request)
    {
        $dto = BrandDTO::fromRequest($request);
        $brand = $this->brandService->createBrand($dto->toArray());
        
        return $this->created(new BrandResource($brand));
    }

    public function show(Brand $brand)
    {
        return $this->ok(new BrandResource($brand));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $dto = BrandDTO::fromRequest($request);
        $this->brandService->updateBrand($brand->id, $dto->toArray());
        
        return $this->ok(new BrandResource($brand->fresh()));
    }

    public function destroy(Brand $brand)
    {
        $this->brandService->deleteBrand($brand->id);
        return $this->noContent();
    }
}
