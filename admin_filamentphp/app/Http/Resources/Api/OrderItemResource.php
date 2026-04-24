<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class OrderItemResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->shop_product_id,
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'type' => $this->type,
        ];
    }
}
