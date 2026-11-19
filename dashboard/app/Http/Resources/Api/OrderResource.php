<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;


#[OAT\Schema(
    schema: 'OrderResource',
    title: 'Order',
    description: 'Chi tiết đơn hàng',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'number', type: 'string', example: 'ORD-2023-001'),
        new OAT\Property(property: 'status', type: 'string', example: 'pending'),
        new OAT\Property(property: 'currency', type: 'string', example: 'VND'),
        new OAT\Property(property: 'subtotal', type: 'number', format: 'float', example: 1000000),
        new OAT\Property(property: 'tax_amount', type: 'number', format: 'float', example: 100000),
        new OAT\Property(property: 'shipping_cost', type: 'number', format: 'float', example: 30000),
        new OAT\Property(property: 'total', type: 'number', format: 'float', example: 1130000),
        new OAT\Property(property: 'shipping_address', ref: '#/components/schemas/OrderAddressResource', nullable: true),
        new OAT\Property(property: 'billing_address', ref: '#/components/schemas/OrderAddressResource', nullable: true),
        new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(ref: '#/components/schemas/OrderItemResource')),
        new OAT\Property(property: 'status_history', type: 'array', items: new OAT\Items(
            properties: [
                new OAT\Property(property: 'status', type: 'string', example: 'pending'),
                new OAT\Property(property: 'time', type: 'string', format: 'date-time')
            ]
        )),
        new OAT\Property(property: 'payment_method', type: 'string', nullable: true),
        new OAT\Property(property: 'payment_status', type: 'string', nullable: true),
        new OAT\Property(property: 'shipping_method', type: 'string', nullable: true),
        new OAT\Property(property: 'discount_total', type: 'number', format: 'float', example: 50000),
        new OAT\Property(property: 'loyalty_discount', type: 'number', format: 'float', example: 10000),
        new OAT\Property(property: 'customer_notes', type: 'string', nullable: true),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class OrderResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $status = $this->enumValue($this->status);
        $latestPayment = $this->relationLoaded('payments')
            ? $this->payments->sortByDesc('id')->first()
            : null;
        $statusHistory = $this->relationLoaded('activities') && $this->activities->isNotEmpty()
            ? $this->activities->sortBy('created_at')->map(fn ($activity) => [
                'status' => $this->enumValue($activity->properties['attributes']['status'] ?? $status),
                'time' => $activity->created_at?->toDateTimeString(),
            ])->values()->toArray()
            : [['status' => $status, 'time' => $this->created_at?->toDateTimeString()]];

        return array_merge([
            'id' => $this->id,
            'number' => $this->number,
            'status' => $status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'shipping_cost' => $this->shipping_cost,
            'total' => $this->total,
            'shipping_address' => new OrderAddressResource($this->whenLoaded('shippingAddress')),
            'billing_address' => new OrderAddressResource($this->whenLoaded('billingAddress')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => $statusHistory,
            'shipping_method' => $this->relationLoaded('shipping') && $this->shipping ? $this->shipping->method : null,
            'payment_method' => $latestPayment ? $this->enumValue($latestPayment->method) : null,
            'payment_status' => $latestPayment ? $this->enumValue($latestPayment->status) : null,
            'customer_notes' => $this->relationLoaded('metas') && $this->metas ? $this->metas->where('key', 'customer_notes')->first()?->value : null,
            'loyalty_discount' => $this->relationLoaded('metas') && $this->metas ? (int) ($this->metas->where('key', 'loyalty_discount')->first()?->value ?? 0) : 0,
            'discount_total' => $this->relationLoaded('coupons') && $this->coupons ? $this->coupons->sum('discount_amount') : 0,
            'coupons' => $this->relationLoaded('coupons') ? $this->coupons->map(fn ($c) => ['code' => $c->coupon_code, 'discount' => $c->discount_amount]) : [],
            'refunds' => $this->relationLoaded('refunds') ? $this->refunds->map(fn ($r) => ['amount' => $r->amount, 'reason' => $r->reason, 'type' => $r->metadata['type'] ?? 'full', 'status' => $r->metadata['status'] ?? 'completed', 'date' => $r->created_at->toDateTimeString()]) : [],
        ], $this->getTimestamps());
    }

    protected function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }
}
