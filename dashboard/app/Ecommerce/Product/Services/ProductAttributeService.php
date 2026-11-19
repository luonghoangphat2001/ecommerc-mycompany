<?php

namespace App\Ecommerce\Product\Services;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductAttributeService
{
    public function paginateGlobalAttributes(bool $onlyEnabled, int $perPage = 50)
    {
        $query = Attribute::query()->withCount('productAttributes');

        if ($onlyEnabled) {
            $query->where('is_enabled', true);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function findAttributeOrFail(int $id): Attribute
    {
        return Attribute::query()->withCount('productAttributes')->findOrFail($id);
    }

    public function updateAttribute(Attribute $attribute, array $data): Attribute
    {
        $attribute->fill(array_filter([
            'name' => isset($data['name']) ? trim((string) $data['name']) : null,
            'type' => $data['type'] ?? null,
            'is_enabled' => array_key_exists('is_enabled', $data) ? (bool) $data['is_enabled'] : null,
            'values' => array_key_exists('values', $data) ? $this->normalizeOptions($data['values'], true) : null,
        ], static fn ($value) => $value !== null));
        $attribute->save();

        return $attribute->loadCount('productAttributes');
    }

    public function deleteAttribute(Attribute $attribute): void
    {
        $attribute->delete();
    }

    public function toggleAttribute(Attribute $attribute, ?bool $enabled = null): Attribute
    {
        $attribute->update([
            'is_enabled' => $enabled ?? ! $attribute->is_enabled,
        ]);

        return $attribute->fresh();
    }

    public function getEnabledAttributes(): Collection
    {
        return Attribute::query()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'type', 'values']);
    }

    public function addValues(Attribute $attribute, array $values): Attribute
    {
        $attribute->mergeValues($values);

        return $attribute->loadCount('productAttributes');
    }

    public function generateVariantPreview(array $data): array
    {
        $baseSku = trim($data['base_sku'] ?? 'PROD');
        $basePrice = (int) ($data['base_price'] ?? 0);
        $arrays = [];

        foreach ($data['attributes'] as $attribute) {
            $name = $attribute['name'];
            $options = array_values(array_filter(
                array_map('trim', $attribute['options']),
                static fn ($option) => $option !== ''
            ));
            if ($options !== []) {
                $arrays[] = array_map(static fn ($option) => [$name => $option], $options);
            }
        }

        if ($arrays === []) {
            return [];
        }

        $combinations = array_reduce($arrays, static function (array $carry, array $current): array {
            $result = [];
            foreach ($carry as $left) {
                foreach ($current as $right) {
                    $result[] = array_merge($left, $right);
                }
            }

            return $result;
        }, [[]]);

        $variants = array_map(static function (array $combination) use ($baseSku, $basePrice): array {
            $suffix = implode('-', array_values($combination));

            return [
                'sku' => strtoupper(preg_replace('/\s+/', '', "{$baseSku}-{$suffix}")),
                'attributes' => $combination,
                'price' => $basePrice,
                'sale_price' => null,
                'qty' => 10,
                'is_active' => true,
            ];
        }, $combinations);

        return [
            'combinations_count' => count($variants),
            'variants' => $variants,
        ];
    }

    /**
     * Retrieve all global attributes, optionally filtering by enabled status.
     */
    public function getGlobalAttributes(bool $onlyEnabled = false): Collection
    {
        $query = Attribute::query()->withCount('productAttributes');

        if ($onlyEnabled) {
            $query->where('is_enabled', true);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Find an existing global attribute by ID or Name/Slug, or create a new one.
     * Merges any newly supplied options into the global attribute's reusable values.
     */
    public function findOrCreateAttribute(
        string $name,
        ?int $attributeId = null,
        array $options = [],
        string $type = 'button'
    ): Attribute {
        $cleanName = trim($name);
        $cleanOptions = $this->normalizeOptions($options, true);

        $globalAttr = null;

        if ($attributeId) {
            $globalAttr = Attribute::find($attributeId);
        }

        if (! $globalAttr) {
            $slug = Str::slug($cleanName);
            $globalAttr = Attribute::where('name', $cleanName)
                ->orWhere('slug', $slug)
                ->first();
        }

        if (! $globalAttr) {
            $slug = Str::slug($cleanName);
            if (empty($slug)) {
                $slug = 'attr-' . time() . '-' . Str::random(4);
            } elseif (Attribute::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . Str::random(4);
            }

            $globalAttr = Attribute::create([
                'name' => $cleanName,
                'slug' => $slug,
                'type' => in_array($type, ['select', 'button', 'color']) ? $type : 'button',
                'values' => $cleanOptions,
                'is_enabled' => true,
            ]);
        } else {
            // Merge newly added values into the reusable global attribute
            if (! empty($cleanOptions)) {
                $globalAttr->mergeValues($cleanOptions);
            }
        }

        return $globalAttr;
    }

    /**
     * Synchronize product attributes:
     * - Validates options
     * - Automatically creates/updates global attributes
     * - Links ProductAttribute to global attribute_id
     */
    public function syncProductAttributes(Product $product, array $attributesInput): array
    {
        $createdAttributes = [];

        // Remove old product-attribute associations
        $product->productAttributes()->delete();

        foreach ($attributesInput as $pos => $attrData) {
            $name = trim((string) ($attrData['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $rawOptions = $attrData['options'] ?? [];
            $options = $this->normalizeOptions($rawOptions);

            if (empty($options)) {
                continue;
            }

            $attrId = ! empty($attrData['attribute_id']) ? (int) $attrData['attribute_id'] : null;
            $type = (string) ($attrData['type'] ?? 'button');
            $isVariation = array_key_exists('is_variation', $attrData)
                ? (bool) $attrData['is_variation']
                : true;

            // Resolve or create the reusable global attribute
            $globalAttr = $this->findOrCreateAttribute($name, $attrId, $options, $type);

            $productAttr = $product->productAttributes()->create([
                'attribute_id' => $globalAttr->id,
                'name' => $name,
                'options' => $options,
                'position' => (int) $pos,
                'is_variation' => $isVariation,
            ]);

            $createdAttributes[] = $productAttr;
        }

        return $createdAttributes;
    }

    /**
     * Synchronize product variants with safety:
     * - Preserves existing variants that match the attribute combinations
     * - Preserves price, sale_price, stock, SKU, and image
     * - Only creates missing combinations
     * - Protects variants referenced by order items from silent deletion
     */
    public function syncProductVariants(Product $product, array $variantsInput): void
    {
        $existingVariants = $product->variants()->get();

        // Check which variant IDs are referenced in orders
        $referencedVariantIds = [];
        if (Schema::hasTable('shop_order_items') && Schema::hasColumn('shop_order_items', 'variant_id')) {
            $referencedVariantIds = DB::table('shop_order_items')
                ->whereIn('variant_id', $existingVariants->pluck('id'))
                ->pluck('variant_id')
                ->unique()
                ->toArray();
        }

        $processedVariantIds = [];
        $totalStock = 0;
        $minPrice = null;

        foreach ($variantsInput as $vData) {
            $vAttrs = is_array($vData['attributes'] ?? null)
                ? $vData['attributes']
                : (json_decode($vData['attributes'] ?? '[]', true) ?: []);

            $vPrice = (int) ($vData['price'] ?? 0);
            $vSalePrice = (! empty($vData['sale_price']) && is_numeric($vData['sale_price']))
                ? (int) $vData['sale_price']
                : null;
            $vSaleStartDate = ! empty($vData['sale_start_date']) ? $vData['sale_start_date'] : null;
            $vSaleEndDate = ! empty($vData['sale_end_date']) ? $vData['sale_end_date'] : null;
            $vQty = (int) ($vData['qty'] ?? 0);
            $vSku = ! empty($vData['sku']) ? trim($vData['sku']) : null;
            $isActive = array_key_exists('is_active', $vData) ? (bool) $vData['is_active'] : true;
            $vCost = (! empty($vData['cost']) && is_numeric($vData['cost'])) ? (int) $vData['cost'] : null;

            // Find an existing variant with matching attributes
            $matchedVariant = null;
            $vAttrKey = $this->buildCombinationKey($vAttrs);

            foreach ($existingVariants as $candidate) {
                if ($this->buildCombinationKey($candidate->attributes ?? []) === $vAttrKey) {
                    $matchedVariant = $candidate;

                    break;
                }
            }

            if ($matchedVariant) {
                // Update existing variant without re-generating ID
                $matchedVariant->update([
                    'sku' => $vSku ?: $matchedVariant->sku,
                    'price' => $vPrice,
                    'sale_price' => $vSalePrice,
                    'sale_start_date' => $vSaleStartDate,
                    'sale_end_date' => $vSaleEndDate,
                    'cost' => $vCost ?: $matchedVariant->cost,
                    'qty' => $vQty,
                    'is_active' => $isActive,
                    'attributes' => $vAttrs,
                ]);

                $processedVariantIds[] = $matchedVariant->id;
            } else {
                // Create new variant
                $newVariant = $product->variants()->create([
                    'sku' => $vSku,
                    'price' => $vPrice,
                    'sale_price' => $vSalePrice,
                    'sale_start_date' => $vSaleStartDate,
                    'sale_end_date' => $vSaleEndDate,
                    'cost' => $vCost,
                    'qty' => $vQty,
                    'attributes' => $vAttrs,
                    'is_active' => $isActive,
                ]);

                $processedVariantIds[] = $newVariant->id;
            }

            if ($isActive) {
                $totalStock += $vQty;
                if ($minPrice === null || ($vPrice > 0 && $vPrice < $minPrice)) {
                    $minPrice = $vPrice;
                }
            }
        }

        // Handle obsolete variants
        foreach ($existingVariants as $oldVariant) {
            if (! in_array($oldVariant->id, $processedVariantIds)) {
                if (in_array($oldVariant->id, $referencedVariantIds)) {
                    // Do NOT delete variants linked to historical orders! Deactivate them safely.
                    $oldVariant->update(['is_active' => false]);
                } else {
                    $oldVariant->delete();
                }
            }
        }

        // Synchronize parent product stock and baseline price
        $updates = ['qty' => $totalStock, 'total_stock' => $totalStock];
        if ($minPrice !== null && $minPrice > 0) {
            $updates['price'] = $minPrice;
        }

        $product->update($updates);
    }

    /**
     * Create a deterministic normalized key for an attribute combination.
     */
    protected function buildCombinationKey(array $attributes): string
    {
        ksort($attributes);

        return json_encode($attributes, JSON_UNESCAPED_UNICODE);
    }

    protected function normalizeOptions(mixed $options, bool $unique = false): array
    {
        $options = is_array($options)
            ? $options
            : explode(',', (string) $options);

        $options = array_values(array_filter(array_map('trim', $options), fn ($option) => $option !== ''));

        return $unique ? array_values(array_unique($options)) : $options;
    }
}
