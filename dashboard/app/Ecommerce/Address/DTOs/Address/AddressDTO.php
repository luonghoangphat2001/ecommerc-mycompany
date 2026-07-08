<?php

namespace App\Ecommerce\Address\DTOs\Address;

class AddressDTO
{
    public function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $company = null,
        public ?string $country_code = null, // ISO 3166-1 alpha-2
        public ?string $state_id = null,
        public ?string $city_id = null,
        public ?string $ward_id = null,
        public ?string $postal_code = null,
        public ?string $address_detail = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            company: $data['company'] ?? null,
            country_code: $data['country_code'] ?? $data['country'] ?? null,
            state_id: $data['state_id'] ?? $data['state'] ?? null,
            city_id: $data['city_id'] ?? $data['city'] ?? null,
            ward_id: $data['ward_id'] ?? $data['ward'] ?? null,
            postal_code: $data['postal_code'] ?? $data['zip'] ?? null,
            address_detail: $data['address_detail'] ?? $data['street'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'country_code' => $this->country_code,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'ward_id' => $this->ward_id,
            'postal_code' => $this->postal_code,
            'address_detail' => $this->address_detail,
        ];
    }
}
