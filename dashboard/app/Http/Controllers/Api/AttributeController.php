<?php

namespace App\Http\Controllers\Api;

use App\Ecommerce\Product\Services\ProductAttributeService;
use App\Http\Resources\Api\AttributeResource;
use App\Models\Attribute;
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
        tags: 'Storefront - Attributes',
        requiresAuth: false,
        responseData: '#/components/schemas/AttributeResource'
    )]
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 50);
        $onlyEnabled = $request->boolean('only_enabled', true);

        $query = Attribute::withCount('productAttributes');
        if ($onlyEnabled) {
            $query->where('is_enabled', true);
        }

        $attributes = $query->orderBy('name')->paginate($perPage);

        return $this->ok(AttributeResource::collection($attributes));
    }

    #[ApiGet(
        path: '/attributes/{id}',
        summary: 'Get Attribute by ID',
        tags: 'Storefront - Attributes',
        requiresAuth: false,
        responseData: '#/components/schemas/AttributeResource'
    )]
    public function show(int $id)
    {
        $attribute = Attribute::withCount('productAttributes')->findOrFail($id);
        return $this->ok(new AttributeResource($attribute));
    }

    #[ApiPost(
        path: '/attributes',
        summary: 'Create Global Attribute',
        tags: 'Storefront - Attributes',
        requiresAuth: true
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:select,button,color'],
            'values' => ['nullable', 'array'],
            'values.*' => ['string'],
        ]);

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
        tags: 'Storefront - Attributes',
        requiresAuth: true
    )]
    public function update(Request $request, int $id)
    {
        $attribute = Attribute::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:select,button,color'],
            'values' => ['nullable', 'array'],
            'values.*' => ['string'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        if (isset($validated['name'])) {
            $attribute->name = trim($validated['name']);
        }
        if (isset($validated['type'])) {
            $attribute->type = $validated['type'];
        }
        if (isset($validated['is_enabled'])) {
            $attribute->is_enabled = (bool) $validated['is_enabled'];
        }
        if (isset($validated['values'])) {
            $attribute->values = array_values(array_unique(array_filter(array_map('trim', $validated['values']))));
        }

        $attribute->save();

        return $this->ok(new AttributeResource($attribute));
    }

    #[ApiDelete(
        path: '/attributes/{id}',
        summary: 'Delete Global Attribute',
        tags: 'Storefront - Attributes',
        requiresAuth: true
    )]
    public function destroy(int $id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return $this->noContent();
    }

    /**
     * Add new values to an existing attribute.
     */
    public function addValues(Request $request, int $id)
    {
        $attribute = Attribute::findOrFail($id);

        $validated = $request->validate([
            'values' => ['required', 'array', 'min:1'],
            'values.*' => ['string'],
        ]);

        $attribute->mergeValues($validated['values']);

        return $this->ok(new AttributeResource($attribute));
    }

    /**
     * Preview/simulate variant combinations from given attributes and options.
     */
    public function generateVariants(Request $request)
    {
        $validated = $request->validate([
            'attributes' => ['required', 'array', 'min:1'],
            'attributes.*.name' => ['required', 'string'],
            'attributes.*.options' => ['required', 'array', 'min:1'],
            'base_sku' => ['nullable', 'string'],
            'base_price' => ['nullable', 'integer'],
        ]);

        $baseSku = trim($validated['base_sku'] ?? 'PROD');
        $basePrice = (int) ($validated['base_price'] ?? 0);

        $arrays = [];
        foreach ($validated['attributes'] as $attr) {
            $name = $attr['name'];
            $opts = array_values(array_filter(array_map('trim', $attr['options']), fn ($o) => $o !== ''));
            if (!empty($opts)) {
                $arrays[] = array_map(fn ($opt) => [$name => $opt], $opts);
            }
        }

        if (empty($arrays)) {
            return $this->badRequest('No valid options found to generate variants.');
        }

        $combinations = array_reduce($arrays, function ($acc, $curr) {
            $res = [];
            foreach ($acc as $a) {
                foreach ($curr as $b) {
                    $res[] = array_merge($a, $b);
                }
            }
            return $res;
        }, [[]]);

        $variants = array_map(function ($combo) use ($baseSku, $basePrice) {
            $suffix = implode('-', array_values($combo));
            return [
                'sku' => strtoupper(preg_replace('/\s+/', '', "{$baseSku}-{$suffix}")),
                'attributes' => $combo,
                'price' => $basePrice,
                'sale_price' => null,
                'qty' => 10,
                'is_active' => true,
            ];
        }, $combinations);

        return $this->ok([
            'combinations_count' => count($variants),
            'variants' => $variants,
        ]);
    }
}
