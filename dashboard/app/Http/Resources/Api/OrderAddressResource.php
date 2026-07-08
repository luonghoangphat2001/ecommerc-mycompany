<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

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
