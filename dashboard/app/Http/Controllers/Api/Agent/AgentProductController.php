<?php

namespace App\Http\Controllers\Api\Agent;

use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\API\TaxRequest;
use App\Http\Resources\Api\TaxCalculationResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentProductController extends BaseApiController
{
    public function __construct(
        protected TaxServiceInterface $taxService
    ) {}

    /**
     * List products with authoritative types and stock for AI Agents.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['variants', 'comboComponents.product', 'comboComponents.variant']);

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%");
            });
        }

        $products = $query->paginate((int) $request->get('per_page', 20));

        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);
        $trackStock = $policy->isInventoryTrackingEnabled();

        $data = $products->getCollection()->map(function (Product $p) use ($trackStock) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'sku' => $p->sku,
                'type' => $p->type?->value ?? 'simple',
                'product_type' => $p->isVariable() ? 'variable' : ($p->isCombo() ? 'combo' : 'simple'),
                'price' => (int) $p->price,
                'sale_price' => $p->is_on_sale ? (int) $p->sale_price : null,
                'effective_price' => (int) $p->effective_price,
                'available_stock' => (int) $p->available_stock,
                'is_available' => $p->is_visible && (!$trackStock || $p->available_stock > 0),
                'tax_class_id' => $p->tax_class_id,
            ];
        });

        return $this->ok([
            'data' => $data,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Authoritative product details for AI Agents.
     */
    public function show($id): JsonResponse
    {
        $product = Product::with([
            'variants',
            'productAttributes',
            'comboComponents.product',
            'comboComponents.variant',
            'taxClass.rates',
        ])->find($id);

        if (!$product) {
            return $this->notFound('Product not found.');
        }

        $country = request()->query('country', 'VN');
        $taxInfo = $this->taxService->calculateProductTax(
            $product,
            request()->query('variant_id'),
            $product->effective_price,
            $country
        );

        $trackStock = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class)->isInventoryTrackingEnabled();

        $payload = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'type' => $product->type?->value ?? 'simple',
            'product_type' => $product->isVariable() ? 'variable' : ($product->isCombo() ? 'combo' : 'simple'),
            'price' => (int) $product->price,
            'sale_price' => $product->is_on_sale ? (int) $product->sale_price : null,
            'effective_price' => (int) $product->effective_price,
            'available_stock' => (int) $product->available_stock,
            'is_available' => (bool) $product->is_visible && (!$trackStock || $product->available_stock > 0),
            'apply_tax' => (bool) $product->apply_tax,
            'tax_class' => $product->taxClass ? [
                'id' => $product->taxClass->id,
                'name' => $product->taxClass->name,
                'rate' => $taxInfo['rate_percent'] ?? 0,
            ] : null,
            'tax_calculation_sample' => $taxInfo,
        ];

        // Variable Product info
        if ($product->isVariable()) {
            $payload['attributes'] = $product->productAttributes->map(fn($attr) => [
                'id' => $attr->id,
                'name' => $attr->name,
                'options' => $attr->options,
            ])->toArray();

            $payload['variants'] = $product->variants->map(function (ProductVariant $v) use ($country, $trackStock) {
                $variantTax = $this->taxService->calculateProductTax($v->product_id, $v->id, $v->effective_price, $country);
                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'price' => (int) $v->price,
                    'sale_price' => $v->sale_price ? (int) $v->sale_price : null,
                    'effective_price' => (int) $v->effective_price,
                    'qty' => (int) $v->qty,
                    'available_stock' => (int) $v->available_stock,
                    'is_available' => (bool) $v->is_active && (!$trackStock || $v->available_stock > 0),
                    'attributes' => $v->attributes,
                    'tax_class_id' => $v->tax_class_id ?? $v->product?->tax_class_id,
                    'tax_rate_percent' => $variantTax['rate_percent'] ?? 0,
                ];
            })->toArray();
        }

        // Combo Product info
        if ($product->isCombo()) {
            $payload['combo_components'] = $product->comboComponents->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->shop_product_id,
                    'variant_id' => $item->product_variant_id,
                    'name' => $item->product?->name ?? 'Component',
                    'quantity_required' => (int) $item->quantity,
                    'unit_price' => (int) $item->unit_price,
                    'available_component_stock' => $item->variant ? (int) $item->variant->available_stock : (int) ($item->product?->available_stock ?? 0),
                    'tax_class_id' => $item->variant?->tax_class_id ?? $item->product?->tax_class_id,
                ];
            })->toArray();

            $comboStock = (int) $product->calculateComboStock();
            $payload['combo_stock_breakdown'] = [
                'available_combos' => $trackStock ? $comboStock : 999999,
                'is_limited_by_stock' => $trackStock && $comboStock <= 0,
            ];
        }

        return $this->ok($payload);
    }

    /**
     * Authoritative tax calculation for product or variant.
     */
    public function tax(TaxRequest $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $variantId = $request->input('variant_id');
        $amount = (int) ($request->input('amount') ?? $product->effective_price);
        $country = $request->input('country', 'VN');
        $state = $request->input('state');
        $city = $request->input('city');

        $calc = $this->taxService->calculateProductTax(
            $product,
            $variantId ? (int) $variantId : null,
            $amount,
            $country,
            $state,
            $city
        );

        return $this->ok(new TaxCalculationResource($calc));
    }
}
