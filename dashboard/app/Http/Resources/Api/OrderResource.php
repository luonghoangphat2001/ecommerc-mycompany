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
        return array_merge([
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'shipping_cost' => $this->shipping_cost,
            'total' => $this->total,
            'shipping_address' => new OrderAddressResource($this->whenLoaded('shippingAddress')),
            'billing_address' => new OrderAddressResource($this->whenLoaded('billingAddress')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => $this->relationLoaded('activities') || $this->activities 
                ? $this->activities()->orderBy('created_at', 'asc')->get()->map(fn($activity) => [
                    'status' => $activity->properties['attributes']['status'] ?? $this->status,
                    'time' => $activity->created_at->toDateTimeString(),
                ])->toArray() 
                : [
                    ['status' => $this->status, 'time' => $this->created_at->toDateTimeString()]
                ],
            'shipping_method' => $this->relationLoaded('shipping') && $this->shipping ? $this->shipping->method : null,
            'payment_method' => $this->relationLoaded('payments') && $this->payments->count() > 0 ? $this->payments->sortByDesc('id')->first()->method : null,
            'payment_status' => $this->relationLoaded('payments') && $this->payments->count() > 0 ? $this->payments->sortByDesc('id')->first()->status : null,
            'customer_notes' => $this->relationLoaded('metas') && $this->metas ? $this->metas->where('key', 'customer_notes')->first()?->value : null,
            'loyalty_discount' => $this->relationLoaded('metas') && $this->metas ? (int) ($this->metas->where('key', 'loyalty_discount')->first()?->value ?? 0) : 0,
            'discount_total' => $this->relationLoaded('coupons') && $this->coupons ? $this->coupons->sum('discount_amount') : 0,
            'coupons' => $this->relationLoaded('coupons') ? $this->coupons->map(fn($c) => ['code' => $c->coupon_code, 'discount' => $c->discount_amount]) : [],
            'refunds' => $this->relationLoaded('refunds') ? $this->refunds->map(fn($r) => ['amount' => $r->amount, 'reason' => $r->reason, 'type' => $r->metadata['type'] ?? 'full', 'status' => $r->metadata['status'] ?? 'completed', 'date' => $r->created_at->toDateTimeString()]) : [],
        ], $this->getTimestamps());
    }
}
