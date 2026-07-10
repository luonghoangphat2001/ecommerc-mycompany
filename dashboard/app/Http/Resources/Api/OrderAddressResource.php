<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'OrderAddressResource',
    title: 'Order Address',
    description: 'Địa chỉ giao hàng/thanh toán',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
        new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
        new OAT\Property(property: 'email', type: 'string', example: 'customer@example.com'),
        new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
        new OAT\Property(property: 'country', type: 'string', example: 'VN'),
        new OAT\Property(property: 'state', type: 'integer', example: 1),
        new OAT\Property(property: 'city', type: 'integer', example: 1),
        new OAT\Property(property: 'ward', type: 'integer', example: 1),
        new OAT\Property(property: 'street', type: 'string', example: '123 Le Loi'),
        new OAT\Property(property: 'type', type: 'string', example: 'shipping'),
    ]
)]
class OrderAddressResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country_code,
            'state' => $this->state_id,
            'city' => $this->city_id,
            'ward' => $this->ward_id,
            'street' => $this->street,
            'type' => $this->type,
        ];
    }
}
