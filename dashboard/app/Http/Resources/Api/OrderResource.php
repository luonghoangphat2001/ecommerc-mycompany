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
        ], $this->getTimestamps());
    }
}
