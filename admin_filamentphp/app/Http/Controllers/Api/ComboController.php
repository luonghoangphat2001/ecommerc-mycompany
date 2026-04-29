<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ComboResource;
use App\Ecommerce\Combo\Contracts\ComboServiceInterface;
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

    protected ComboServiceInterface $comboService;

    public function __construct(ComboServiceInterface $comboService)
    {
        $this->comboService = $comboService;
    }

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
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);

        $combos = $this->comboService->getActiveCombos();

        return $this->ok(ComboResource::collection($combos->paginate($validated['per_page'] ?? 15)));
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
        $combo = $this->comboService->getComboBySlug($slug);

        if (!$combo) {
            return $this->notFound(__('messages.api.combo_not_found'));
        }

        return $this->ok(new ComboResource($combo));
    }
}
