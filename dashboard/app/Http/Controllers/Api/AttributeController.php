<?php

namespace App\Http\Controllers\Api;

use App\Ecommerce\Product\Services\ProductAttributeService;
use App\Http\Requests\API\AttributeRequest;
use App\Http\Requests\API\AttributeValuesRequest;
use App\Http\Requests\API\GenerateVariantsRequest;
use App\Http\Resources\Api\AttributeResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class AttributeController extends BaseApiController
{
    public function __construct(
        protected ProductAttributeService $attributeService
    ) {}

    #[ApiList(
        path: '/attributes',
        summary: 'List of Global Attributes',
        tags: 'Attributes',
        requiresAuth: false,
        responseData: '#/components/schemas/AttributeResource'
    )]


    public function index(Request $request)
    {
        $attributes = $this->attributeService->paginateGlobalAttributes(
            $request->boolean('only_enabled', true),
            (int) $request->get('per_page', 50)
        );

        return $this->ok(AttributeResource::collection($attributes));
    }

    #[ApiGet(
        path: '/attributes/{id}',
        summary: 'Get Attribute by ID',
        tags: 'Attributes',
        requiresAuth: false,
        responseData: '#/components/schemas/AttributeResource'
    )]


    public function show(int $id)
    {
        $attribute = $this->attributeService->findAttributeOrFail($id);

        return $this->ok(new AttributeResource($attribute));
    }

    #[ApiPost(
        path: '/attributes',
        summary: 'Create Global Attribute',
        tags: 'Attributes',
        requiresAuth: true
    )]


    public function store(AttributeRequest $request)
    {
        $validated = $request->validated();

        $attribute = $this->attributeService->findOrCreateAttribute(
            $validated['name'],
            null,
            $validated['values'] ?? [],
            $validated['type'] ?? 'button'
        );

        return $this->created(new AttributeResource($attribute));
    }

    #[ApiUpdate(
        path: '/attributes/{id}',
        summary: 'Update Global Attribute',
        tags: 'Attributes',
        requiresAuth: true
    )]


    public function update(AttributeRequest $request, int $id)
    {
        $attribute = $this->attributeService->findAttributeOrFail($id);

        $validated = $request->validated();

        $attribute = $this->attributeService->updateAttribute($attribute, $validated);

        return $this->ok(new AttributeResource($attribute));
    }

    #[ApiDelete(
        path: '/attributes/{id}',
        summary: 'Delete Global Attribute',
        tags: 'Attributes',
        requiresAuth: true
    )]


    public function destroy(int $id)
    {
        $attribute = $this->attributeService->findAttributeOrFail($id);
        $this->attributeService->deleteAttribute($attribute);

        return $this->noContent();
    }

    /**
     * Add new values to an existing attribute.
     */
    public function addValues(AttributeValuesRequest $request, int $id)
    {
        $attribute = $this->attributeService->findAttributeOrFail($id);

        $validated = $request->validated();

        $attribute = $this->attributeService->addValues($attribute, $validated['values']);

        return $this->ok(new AttributeResource($attribute));
    }

    /**
     * Preview/simulate variant combinations from given attributes and options.
     */
    public function generateVariants(GenerateVariantsRequest $request)
    {
        $validated = $request->validated();

        $preview = $this->attributeService->generateVariantPreview($validated);
        if ($preview === []) {
            return $this->badRequest('No valid options found to generate variants.');
        }

        return $this->ok($preview);
    }
}
