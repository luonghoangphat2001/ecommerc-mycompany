<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Ecommerce\Coupon\Contracts\CouponServiceInterface;
use App\Exceptions\CouponValidationException;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CouponServiceInterface $couponService
    ) {}

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
