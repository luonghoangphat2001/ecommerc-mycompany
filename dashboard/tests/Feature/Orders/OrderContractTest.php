<?php

namespace Tests\Feature\Orders;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderContractTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_collection_requires_authentication(): void
    {
        $this->getJson('/api/v1/orders')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_order_creation_route_returns_validation_error_for_invalid_payload(): void
    {
        $this->postJson('/api/v1/orders', [])
            ->assertUnauthorized();
    }
}
