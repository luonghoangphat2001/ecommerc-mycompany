<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

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
