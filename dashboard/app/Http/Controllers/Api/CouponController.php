<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\API\CouponRequest;
use App\Http\Controllers\Api\BaseApiController;
use App\Ecommerce\Coupon\Contracts\CouponServiceInterface;
use App\Exceptions\CouponValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiPost;

class CouponController extends BaseApiController
{

    public function __construct(
        protected CouponServiceInterface $couponService
    ) {}

    #[ApiPost(
        path: '/coupons/apply',
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
    public function apply(CouponRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = $validated['code'];
        $items = $validated['items'];
        $subtotal = (float)$validated['subtotal'];
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

    public function index(Request $request): JsonResponse
    {
        if (!app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class)->isCouponsEnabled()) {
            return $this->ok([]);
        }

        $coupons = \App\Models\Coupon::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn ($coupon) => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type instanceof \BackedEnum ? $coupon->type->value : $coupon->type,
                'value' => (float) $coupon->value,
                'min_order_amount' => (float) ($coupon->min_order_amount ?? 0),
                'max_discount_amount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
                'starts_at' => $coupon->starts_at?->toIso8601String(),
                'expires_at' => $coupon->expires_at?->toIso8601String(),
            ]);

        return $this->ok($coupons);
    }

    public function myUsages(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->unauthorized();
        }

        $usages = \App\Models\OrderCoupon::query()
            ->with(['order', 'coupon'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('id')
            ->get()
            ->map(fn ($usage) => [
                'id' => $usage->id,
                'order_id' => $usage->order_id,
                'order_number' => $usage->order?->number,
                'code' => $usage->coupon_code,
                'discount_amount' => (float) $usage->discount_amount,
                'used_at' => $usage->created_at?->toIso8601String(),
            ]);

        return $this->ok($usages);
    }
}
