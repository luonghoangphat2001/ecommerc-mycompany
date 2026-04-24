<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $locationService = app(\App\Contracts\Services\LocationServiceInterface::class);
        $countries = $locationService->getCountryOptions();
        $countryCode = !empty($countries) ? array_rand($countries) : 'VN';

        return [
            'country_code' => $countryCode,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address_detail' => $this->faker->streetAddress(),
            'state_id' => 'Thành phố Hồ Chí Minh',
            'city_id' => 'Quận 1',
            'ward_id' => 'Phường Bến Nghé',
            'postal_code' => $this->faker->postcode(),
            'is_default' => false,
        ];
    }
}
