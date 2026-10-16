<?php

namespace Tests\Unit;

use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\InventoryResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequestAndResourceTest extends TestCase
{
    public function test_login_request_validation(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $invalidData = ['email' => 'invalid-email', 'password' => ''];
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());

        $validData = [
            'email' => 'user@example.com',
            'password' => 'secret123',
            'device_name' => 'iphone-14',
        ];
        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_update_profile_request_validation(): void
    {
        $request = new UpdateProfileRequest();
        $rules = $request->rules();

        $invalidData = [
            'gender' => 'invalid_gender',
            'email' => 'not-an-email',
        ];
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());

        $validData = [
            'name' => 'Nguyen Van A',
            'email' => 'valid@example.com',
            'phone' => '0912345678',
            'gender' => 'male',
        ];
        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_user_resource_structure(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0987654321',
            'gender' => 'male',
        ]);
        $user->id = 1;

        $resource = new UserResource($user);
        $response = $resource->toArray(request());

        $this->assertEquals(1, $response['id']);
        $this->assertEquals('John Doe', $response['name']);
        $this->assertEquals('john@example.com', $response['email']);
        $this->assertEquals('0987654321', $response['phone']);
        $this->assertEquals('male', $response['gender']);
        $this->assertArrayNotHasKey('password', $response);
    }

    public function test_inventory_resource_backward_compatibility(): void
    {
        $inventory = new Inventory();
        $inventory->id = 5;
        $inventory->name = 'Kho HCM';
        $inventory->slug = 'kho-hcm';
        $inventory->location = 'Q1, TP HCM';
        $inventory->is_active = true;

        $resource = new InventoryResource($inventory);
        $response = $resource->toArray(request());

        $this->assertEquals(5, $response['id']);
        $this->assertEquals(5, $response['warehouse_id']);
        $this->assertEquals('Kho HCM', $response['name']);
        $this->assertEquals('Kho HCM', $response['warehouse_name']);
        $this->assertEquals('Q1, TP HCM', $response['location']);
        $this->assertEquals('Q1, TP HCM', $response['warehouse_location']);
        $this->assertTrue($response['is_active']);
    }

    public function test_enum_cases_integrity(): void
    {
        $this->assertEquals('pending', \App\Ecommerce\Order\Enums\OrderStatus::Pending->value);
        $this->assertEquals('paid', \App\Enums\PaymentStatus::Paid->value);
        $this->assertEquals('fixed_cart', \App\Enums\CouponType::FixedCart->value);
        $this->assertEquals('product', \App\Enums\OrderItemType::Product->value);
        $this->assertEquals('shipping', \App\Enums\AddressType::Shipping->value);
        $this->assertEquals('flat_rate', \App\Enums\ShippingMethodType::FlatRate->value);
    }

    public function test_services_resolve_cleanly_via_container_di(): void
    {
        $calculator = app(\App\Ecommerce\Checkout\Services\CheckoutCalculatorService::class);
        $this->assertInstanceOf(\App\Ecommerce\Checkout\Services\CheckoutCalculatorService::class, $calculator);

        $couponService = app(\App\Ecommerce\Coupon\Contracts\CouponServiceInterface::class);
        $this->assertInstanceOf(\App\Ecommerce\Coupon\Contracts\CouponServiceInterface::class, $couponService);

        $productService = app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class);
        $this->assertInstanceOf(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class, $productService);
    }
}

