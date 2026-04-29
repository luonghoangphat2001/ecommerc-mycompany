<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ComboResource;
use App\Models\ComboProduct;
use App\Settings\MarketingSettings;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Marketing
 *
 * APIs for managing combo products
 */
class ComboController extends Controller
{
    use ApiResponse;

    /**
     * Get all active combo products.
     *
     * Returns a paginated list of active combo products.
     *
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Summer Combo",
     *       "slug": "summer-combo",
     *       "combo_price": 800000,
     *       "original_price": 1200000,
     *       "discount_percent": 33
     *     }
     *   ],
     *   "links": {},
     *   "meta": {}
     * }
     */
    public function index(Request $request): JsonResponse
    {
        if (!app(MarketingSettings::class)->combo_enabled) {
            return $this->ok([]);
        }

        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);

        $combos = ComboProduct::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->with(['items.product'])
            ->orderBy('sort_order', 'asc')
            ->paginate($validated['per_page'] ?? 15);

        return $this->ok(ComboResource::collection($combos));
    }

    /**
     * Get combo product detail by slug.
     *
     * Returns detailed information about a specific combo product.
     *
     * @urlParam slug string required The slug of the combo. Example: summer-combo
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Summer Combo",
     *     "slug": "summer-combo",
     *     "combo_price": 800000,
     *     "original_price": 1200000,
     *     "discount_percent": 33,
     *     "items": [
     *       {
     *         "id": 1,
     *         "product": {},
     *         "quantity": 2
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "message": "Combo product not found."
     * }
     */
    public function show(string $slug): JsonResponse
    {
        if (!app(MarketingSettings::class)->combo_enabled) {
            return $this->notFound(__('messages.api.combo_not_found'));
        }

        $combo = ComboProduct::where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->with(['items.product'])
            ->first();

        if (!$combo) {
            return $this->notFound(__('messages.api.combo_not_found'));
        }

        return $this->ok(new ComboResource($combo));
    }
}
