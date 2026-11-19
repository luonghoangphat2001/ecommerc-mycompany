<?php

namespace App\Http\Requests\Admin;

class StoreRefundRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'disposition' => ['nullable', 'in:none,restockable,damaged'],
            'items' => ['nullable', 'array'],
            'items.*.order_item_id' => ['required_with:items', 'integer'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ];
    }
}
