<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    protected OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new OrderPolicy();
    }

    public function test_customer_can_view_own_order(): void
    {
        $user = new User();
        $user->id = 10;

        $order = new Order();
        $order->user_id = 10;

        $this->assertTrue($this->policy->view($user, $order));
    }

    public function test_customer_cannot_view_other_users_order(): void
    {
        $user = new User();
        $user->id = 10;

        $order = new Order();
        $order->user_id = 99;

        // User does not own order and has no permissions
        $this->assertFalse($this->policy->view($user, $order));
    }

    public function test_customer_can_update_own_order(): void
    {
        $user = new User();
        $user->id = 10;

        $order = new Order();
        $order->user_id = 10;

        $this->assertTrue($this->policy->update($user, $order));
    }

    public function test_customer_cannot_update_other_users_order(): void
    {
        $user = new User();
        $user->id = 10;

        $order = new Order();
        $order->user_id = 99;

        $this->assertFalse($this->policy->update($user, $order));
    }
}

