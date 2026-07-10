<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Ecommerce\Coupon\Contracts\CouponServiceInterface;
use App\Exceptions\CouponValidationException;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiPost;

class CouponController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CouponServiceInterface $couponService
    ) {}

    #[ApiPost(
        path: '/api/storefront/v1/coupons/apply',
        summary: 'Apply Coupon',
        tags: 'Storefront - Coupons',
        requiresAuth: true,
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['code', 'items', 'subtotal'],
                properties: [
                    new OAT\Property(property: 'code', type: 'string', example: 'SUMMER24'),
                    new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(type: 'object')),
                    new OAT\Property(property: 'subtotal', type: 'number', format: 'float', example: 1500000)
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'code', type: 'string', example: 'SUMMER24'),
            new OAT\Property(property: 'discount_amount', type: 'number', format: 'float', example: 50000),
            new OAT\Property(property: 'type', type: 'string', example: 'fixed')
        ]
    )]
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'items' => 'required|array',
            'subtotal' => 'required|numeric',
        ]);

        $code = $request->input('code');
        $items = $request->input('items');
        $subtotal = (float)$request->input('subtotal');
        $userId = auth('sanctum')->id();

        try {
            // 1. Validate logic
            $dto = $this->couponService->validateCoupon($code, $items, $subtotal, $userId);
            
            // 2. Calculate discount amount
            $discountAmount = $this->couponService->calculateDiscount($dto->coupon, $items, $subtotal);

            return $this->success([
                'code' => $dto->coupon->code,
                'discount_amount' => $discountAmount,
                'type' => $dto->coupon->type,
            ], trans('messages.coupon_applied_success'));

        } catch (CouponValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
