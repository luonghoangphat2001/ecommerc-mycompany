<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'UserResource',
    title: 'User Profile',
    description: 'Thông tin hồ sơ người dùng',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Nguyen Van A'),
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'customer@example.com'),
        new OAT\Property(property: 'phone', type: 'string', nullable: true, example: '0123456789'),
        new OAT\Property(property: 'photo', type: 'string', nullable: true, example: 'https://example.com/avatar.jpg'),
        new OAT\Property(property: 'default_shipping_address_id', type: 'integer', nullable: true, example: 1),
        new OAT\Property(property: 'default_billing_address_id', type: 'integer', nullable: true, example: 2),
    ]
)]
class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'photo' => $this->photo,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'bio' => $this->bio,
            'default_shipping_address_id' => $this->default_shipping_address_id,
            'default_billing_address_id' => $this->default_billing_address_id,
            'default_shipping_address' => $this->whenLoaded('defaultShippingAddress', fn() => new AddressResource($this->defaultShippingAddress)),
            'default_billing_address' => $this->whenLoaded('defaultBillingAddress', fn() => new AddressResource($this->defaultBillingAddress)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
