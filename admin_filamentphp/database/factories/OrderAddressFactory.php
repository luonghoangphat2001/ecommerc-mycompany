<?php

namespace Database\Factories;

use App\Models\OrderAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderAddressFactory extends Factory
{
    protected $model = OrderAddress::class;

    public function definition(): array
    {
        $locationService = app(\App\Ecommerce\Location\Contracts\LocationServiceInterface::class);
        $countries = $locationService->getCountryOptions();
        $countryCode = !empty($countries) ? array_rand($countries) : 'VN';

        return [
            'addressable_type' => \App\Models\Order::class,
            'addressable_id' => 1,
            'country_code' => $countryCode,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address_detail' => $this->faker->streetAddress(),
            'state_id' => 'Thành phố Hồ Chí Minh',
            'city_id' => 'Quận 1',
            'ward_id' => 'Phường Bến Nghé',
            'postal_code' => $this->faker->postcode(),
            'company' => $this->faker->company(),
            'type' => 'shipping', 
        ];
    }
}
