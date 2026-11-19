<?php

namespace App\Http\Requests\Admin;

use App\Ecommerce\Order\Enums\OrderStatus;
use Illuminate\Validation\Rules\Enum;

class OrderStatusRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return ['status' => ['required', new Enum(OrderStatus::class)]];
    }
}
