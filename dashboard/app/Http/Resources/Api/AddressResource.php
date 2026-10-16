<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'AddressResource',
    title: 'User Address',
    description: 'Địa chỉ người dùng',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'type', type: 'string', example: 'shipping'),
        new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
        new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
        new OAT\Property(property: 'company', type: 'string', nullable: true, example: 'HP Platform'),
        new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
        new OAT\Property(property: 'email', type: 'string', nullable: true, example: 'customer@example.com'),
        new OAT\Property(property: 'country_code', type: 'string', example: 'VN'),
        new OAT\Property(property: 'street', type: 'string', example: '123 Le Loi'),
        new OAT\Property(property: 'city_id', type: 'string', nullable: true, example: 'HCM'),
        new OAT\Property(property: 'state_id', type: 'string', nullable: true, example: 'SG'),
        new OAT\Property(property: 'ward_id', type: 'string', nullable: true, example: 'W1'),
        new OAT\Property(property: 'zip', type: 'string', nullable: true, example: '700000'),
    ]
)]
class AddressResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'type' => $this->type instanceof \BackedEnum ? $this->type->value : $this->type,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'phone' => $this->phone,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'street' => $this->street,
            'city_id' => $this->city_id,
            'state_id' => $this->state_id,
            'ward_id' => $this->ward_id,
            'zip' => $this->zip ?? $this->postal_code,
        ], $this->getTimestamps());
    }
}
